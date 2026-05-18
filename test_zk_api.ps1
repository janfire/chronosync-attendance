$port = 22001
$paths = @(
    "/",
    "/info",
    "/device",
    "/fingerprint/capture",
    "/zkbio/info",
    "/api/device",
    "/biometrics/capture",
    "/ZKBioOnline/info",
    "/finger/capture",
    "/site/capture",
    "/reg",
    "/verify",
    "/enroll",
    "/capture",
    "/iZHost/info",
    "/zkbio/capture"
)

foreach ($path in $paths) {
    try {
        $uri = "http://127.0.0.1:$port$path"
        $response = Invoke-RestMethod -Uri $uri -Method GET -ErrorAction SilentlyContinue
        Write-Host "GET $uri : Success"
        if ($response) { Write-Host ($response | ConvertTo-Json -Depth 1) }
    } catch {
        Write-Host "GET $uri : Failed ($($_.Exception.Message))"
    }

    try {
        $uri = "http://127.0.0.1:$port$path"
        $response = Invoke-RestMethod -Uri $uri -Method POST -ErrorAction SilentlyContinue
        Write-Host "POST $uri : Success"
        if ($response) { Write-Host ($response | ConvertTo-Json -Depth 1) }
    } catch {
        Write-Host "POST $uri : Failed ($($_.Exception.Message))"
    }
}
