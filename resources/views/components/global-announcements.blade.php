@php
    $announcements = \App\Models\GlobalAnnouncement::getActive();
@endphp

@if($announcements->isNotEmpty())
    <div id="global-announcements-container" class="space-y-2 mb-4 w-full">
        @foreach($announcements as $announcement)
            @php
                $colors = [
                    'info' => 'bg-blue-50 border-blue-200 text-blue-800',
                    'warning' => 'bg-orange-50 border-orange-200 text-orange-800',
                    'success' => 'bg-green-50 border-green-200 text-green-800',
                    'danger' => 'bg-red-50 border-red-200 text-red-800',
                ];
                $icons = [
                    'info' => 'fa-info-circle text-blue-500',
                    'warning' => 'fa-exclamation-triangle text-orange-500',
                    'success' => 'fa-check-circle text-green-500',
                    'danger' => 'fa-exclamation-circle text-red-500',
                ];
                $colorClass = $colors[$announcement->type] ?? $colors['info'];
                $iconClass = $icons[$announcement->type] ?? $icons['info'];
            @endphp
            
            <div class="global-announcement-banner border rounded-xl p-4 flex items-start sm:items-center gap-3 shadow-sm relative transition-opacity duration-300 {{ $colorClass }}" 
                 data-id="{{ $announcement->id }}" 
                 style="display: none;">
                
                <div class="mt-0.5 sm:mt-0 shrink-0">
                    <i class="fas {{ $iconClass }} text-lg"></i>
                </div>
                
                <div class="flex-1 min-w-0 pr-8">
                    <h4 class="font-bold text-sm">{{ $announcement->title }}</h4>
                    <p class="text-sm opacity-90 mt-0.5">{{ $announcement->message }}</p>
                </div>
                
                <button type="button" class="dismiss-announcement absolute right-3 top-3 sm:top-1/2 sm:-translate-y-1/2 p-1.5 rounded-lg opacity-60 hover:opacity-100 hover:bg-black/5 transition-all" aria-label="Dismiss">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endforeach
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const banners = document.querySelectorAll('.global-announcement-banner');
            let visibleCount = 0;
            
            banners.forEach(banner => {
                const id = banner.getAttribute('data-id');
                const isDismissed = localStorage.getItem('dismissed_announcement_' + id);
                
                if (!isDismissed) {
                    banner.style.display = 'flex';
                    visibleCount++;
                }
                
                const dismissBtn = banner.querySelector('.dismiss-announcement');
                if (dismissBtn) {
                    dismissBtn.addEventListener('click', function() {
                        localStorage.setItem('dismissed_announcement_' + id, 'true');
                        banner.style.opacity = '0';
                        setTimeout(() => {
                            banner.style.display = 'none';
                        }, 300);
                    });
                }
            });
            
            if (visibleCount === 0) {
                const container = document.getElementById('global-announcements-container');
                if (container) container.style.display = 'none';
            }
        });
    </script>
@endif
