$port = 22001
$paths = @(
    "/iZHost/fingerprint/capture",
    "/iZHost/UDevice/capture",
    "/UDevice/capture",
    "/bio/capture",
    "/finger/capture",
    "/api/fingerprint/capture"
)

foreach ($path in $paths) {
    try {
        $uri = "http://127.0.0.1:$port$path"
        $response = Invoke-RestMethod -Uri $uri -Method POST -ErrorAction SilentlyContinue
        Write-Host "POST $uri : Success"
        if ($response) { Write-Host ($response | ConvertTo-Json -Depth 1) }
    } catch {
        Write-Host "POST $uri : Failed ($($_.Exception.Message))"
    }
}
