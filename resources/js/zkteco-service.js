export default class ZKTecoService {
    constructor() {
        this.socket = null;
        this.isConnected = false;
        this.requests = new Map();
        this.wsUrl = 'ws://127.0.0.1:22003'; // Port for ZK agent
    }

    async checkService() {
        try {
            await this.connect();
            return true;
        } catch (error) {
            console.error('ZK Service Check Failed:', error);
            return false;
        }
    }

    connect() {
        if (this.isConnected && this.socket && this.socket.readyState === WebSocket.OPEN) {
            return Promise.resolve();
        }

        return new Promise((resolve, reject) => {
            try {
                this.socket = new WebSocket(this.wsUrl);

                this.socket.onopen = () => {
                    // console.log('Connected to ZK Agent');
                    this.isConnected = true;
                    resolve();
                };

                this.socket.onclose = () => {
                    // console.log('Disconnected from ZK Agent');
                    this.isConnected = false;
                    this.socket = null;
                };

                this.socket.onerror = (error) => {
                    reject(error);
                };

                this.socket.onmessage = (event) => this.handleMessage(event);

            } catch (error) {
                reject(error);
            }
        });
    }

    handleMessage(event) {
        // console.log('ZK Agent Message received');
        try {
            const data = JSON.parse(event.data);

            // Handle Open Response
            if (this.requests.has('open')) {
                if (data.function === 'open' || (data.ret === 0 && !data.template)) {
                    const { resolve, reject } = this.requests.get('open');
                    if (data.ret === 0) {
                        this.requests.delete('open');
                        resolve(data);
                        return;
                    } else {
                        // console.warn('Open Device failed:', data);
                        // this.requests.delete('open');
                        // reject(new Error('Failed to open device: ' + data.ret));
                        // ZK Agent sometimes sends open success late, just ignore fail for now if it works
                    }
                }
            }

            // Handle Capture Response (Verification & Enrollment-via-Capture)
            if (this.requests.has('capture')) {
                const req = this.requests.get('capture');

                if (data.template || (data.data && data.data.template) || (data.ret === 0 && data.function === 'capture')) {
                    const tpl = data.template || (data.data ? data.data.template : null);
                    const img = data.img || (data.data ? data.data.img : null);

                    if (tpl) {
                        this.requests.delete('capture');
                        req.resolve({ template: tpl, image: img });
                        return;
                    }
                }
            }

            // Handle Register Response (Legacy/Multi-step)
            if (this.requests.has('register')) {
                const req = this.requests.get('register');

                if (data.function === 'onenroll' && data.data) {
                    const enrollIndex = data.data.enroll_index;
                    if (typeof enrollIndex !== 'undefined' && req.onProgress) {
                        req.onProgress(enrollIndex);
                    }
                    return;
                }

                if (data.template || (data.data && data.data.template) || (data.ret === 0 && data.function === 'register')) {
                    const tpl = data.template || (data.data ? data.data.template : null);
                    const img = data.img || (data.data ? data.data.img : null);

                    if (tpl) {
                        this.requests.delete('register');
                        req.resolve({ template: tpl, image: img });
                        return;
                    }
                }
            }

            // Handle Verify Response
            if (this.requests.has('verify')) {
                const req = this.requests.get('verify');
                // verify returns ret=0 for match, ret!=0 for no match
                // or sometimes {data: {grade: X}}

                if (data.function === 'verify') {
                    if (this.requests.has('verify')) {
                        const req = this.requests.get('verify');
                        this.requests.delete('verify');

                        if (data.ret === 0) {
                            // Match!
                            req.resolve(data.data ? data.data.grade : true);
                        } else {
                            // No match
                            req.resolve(false);
                        }
                    }
                }
            }
        } catch (e) {
            console.error('Error parsing WebSocket message:', e);
        }
    }

    async openDevice() {
        if (!this.isConnected) await this.connect();

        return new Promise((resolve, reject) => {
            const openCmd = {
                "module": "fingerprint",
                "function": "open",
                "data": JSON.stringify({ "timeout": 5000 })
            };
            this.requests.set('open', { resolve, reject });
            try {
                this.socket.send(JSON.stringify(openCmd));
                // console.log('Sent Open command');
                // Fallback resolve if no response
                setTimeout(() => {
                    if (this.requests.has('open')) {
                        this.requests.delete('open');
                        resolve(true); // Assume open worked or was already open
                    }
                }, 2000);
            } catch (e) {
                this.requests.delete('open');
                reject(e);
            }
        });
    }

    async captureFingerprint(onProgress) {
        if (!this.isConnected) await this.checkService();
        await this.openDevice();

        // Use 'capture' instead of 'register' to ensure template consistency
        // This is a single-step capture. We simulate the 3-step progress for the UI.

        return new Promise((resolve, reject) => {
            this.requests.set('capture', { resolve, reject });

            const cmd = {
                "module": "fingerprint",
                "function": "capture",
                "data": JSON.stringify({
                    "timeout": 20000,
                    "quality": 50
                })
            };

            try {
                this.socket.send(JSON.stringify(cmd));
                // console.log('Sent Capture command');

                // Simulate progress for UI feedback
                if (onProgress) {
                    onProgress(1);
                    setTimeout(() => onProgress(2), 200);
                    setTimeout(() => onProgress(3), 400);
                }

                setTimeout(() => {
                    if (this.requests.has('capture')) {
                        this.requests.delete('capture');
                        reject(new Error('Capture timed out. Please try again.'));
                    }
                }, 25000);

            } catch (e) {
                this.requests.delete('capture');
                reject(e);
            }
        });
    }

    async identifyFingerprint() {
        if (!this.isConnected) await this.checkService();
        await this.openDevice();

        // 1. Fetch templates if not already loaded
        if (!this.templates || this.templates.length === 0) {
            // console.log('Fetching templates for client-side matching...');
            await this.fetchTemplates();
        }

        // 2. Capture One Fingerprint
        const captured = await this.captureTemplate();
        if (!captured) throw new Error('Capture failed');

        // console.log('Fingerprint captured.');

        // 3. Client-Side 1:N Matching Loop
        let bestMatch = null;
        let highestScore = 0;

        // console.log('Starting 1:N matching...');

        for (const user of this.templates) {
            try {
                // Skip if template is invalid
                if (!user.fingerprint_template || user.fingerprint_template.length < 50) continue;

                // Checking verifyMatch against everyone is safer for accuracy but slower.
                // Since this runs on localhost client, it should be acceptable for < 500 users.

                const score = await this.verifyMatch(captured, user.fingerprint_template);

                // If match is successful (score is usually returned as 'grade' or true)
                // Note: verifyMatch resolves with: score (number) OR true (boolean) OR false (no match)
                if (score !== false && score !== null && score !== undefined) {
                    const numericScore = typeof score === 'number' ? score : 0;
                    // console.log('Match candidate checked');

                    if (numericScore >= highestScore) {
                        highestScore = numericScore;
                        bestMatch = {
                            success: true,
                            user_id: user.user_id,
                            matched_template: user.fingerprint_template,
                            match_score: numericScore
                        };
                    }
                }
            } catch (e) {
                // Ignore individual verify errors
            }
        }

        if (bestMatch) {
            // console.log('Best Match Found!');
            return bestMatch;
        }

        console.warn('No match found after checking all templates.');
        return null;
    }

    async captureTemplate() {
        return new Promise((resolve, reject) => {
            const reqId = 'capture_' + Date.now();
            // We reuse 'capture' key for handleMessage compatibility
            this.requests.set('capture', { resolve, reject });

            const cmd = {
                "module": "fingerprint",
                "function": "capture",
                "data": JSON.stringify({ "timeout": 10000, "quality": 50 })
            };

            try {
                this.socket.send(JSON.stringify(cmd));
                setTimeout(() => {
                    if (this.requests.has('capture')) {
                        this.requests.delete('capture');
                        reject(new Error('Capture timed out'));
                    }
                }, 12000);
            } catch (e) {
                this.requests.delete('capture');
                reject(e);
            }
        }).then(res => res.template);
    }

    async verifyMatch(captured, stored) {
        return new Promise((resolve, reject) => {
            this.requests.set('verify', { resolve, reject });

            const cmd = {
                "module": "fingerprint",
                "function": "verify",
                "data": JSON.stringify({
                    "template1": captured,
                    "template2": stored
                })
            };

            this.socket.send(JSON.stringify(cmd));
        });
    }

    async fetchTemplates() {
        try {
            const res = await fetch('/biometric/templates');
            const data = await res.json();
            if (data.success) {
                this.templates = data.data;
                // console.log('Loaded templates.');
            }
        } catch (e) {
            console.error('Failed to load templates:', e);
            this.templates = [];
        }
    }

    async verify(template) { }
    async cancelCapture() { }
}
