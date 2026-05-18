$viewsPath = "c:\Users\M.T\Desktop\zou-attendance\resources\views"
$files = Get-ChildItem -Path $viewsPath -Recurse -File -Filter "*.blade.php"

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw

    # Favicon
    $content = $content -replace '<link rel="icon" type="image/jpeg" href="\{\{ asset\(''images/logo/zou-logo.jpg''\) \}\}">', '<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⏱️</text></svg>">'

    # Regular images. I will use regex to find the img tags and replace them with a div.
    $content = $content -replace '<img src="\{\{ asset\(''images/logo/zou-logo.jpg''\) \}\}"[^>]*>', '<div class="h-12 w-12 bg-emerald-500 rounded-xl flex items-center justify-center text-white shadow-lg shrink-0"><i class="fas fa-clock text-2xl"></i></div>'

    Set-Content -Path $file.FullName -Value $content
}
