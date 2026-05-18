$viewsPath = "c:\Users\M.T\Desktop\zou-attendance\resources\views"
$files = Get-ChildItem -Path $viewsPath -Recurse -File -Filter "*.blade.php"

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw

    # Branding text
    $content = $content.Replace('ZOU Attendance', 'ChronoSync Attendance')
    $content = $content.Replace('ZOU', 'ChronoSync')
    $content = $content.Replace('@zou.ac.zw', '@example.com')
    $content = $content.Replace('zou.ac.zw', 'example.com')

    # Rebranding colors
    # Backgrounds
    $content = $content.Replace('bg-blue-900', 'bg-gray-900')
    $content = $content.Replace('bg-blue-800', 'bg-gray-800')
    
    $content = $content.Replace('bg-blue-700', 'bg-emerald-700')
    $content = $content.Replace('bg-blue-600', 'bg-emerald-600')
    $content = $content.Replace('bg-blue-500', 'bg-emerald-500')
    $content = $content.Replace('bg-blue-400', 'bg-emerald-400')
    $content = $content.Replace('bg-blue-300', 'bg-emerald-300')
    $content = $content.Replace('bg-blue-200', 'bg-emerald-200')
    $content = $content.Replace('bg-blue-100', 'bg-emerald-100')
    $content = $content.Replace('bg-blue-50', 'bg-emerald-50')

    # Text
    $content = $content.Replace('text-blue-900', 'text-gray-900')
    $content = $content.Replace('text-blue-800', 'text-emerald-800')
    $content = $content.Replace('text-blue-700', 'text-emerald-700')
    $content = $content.Replace('text-blue-600', 'text-emerald-600')
    $content = $content.Replace('text-blue-500', 'text-emerald-500')
    $content = $content.Replace('text-blue-400', 'text-emerald-400')
    $content = $content.Replace('text-blue-100', 'text-emerald-100')
    $content = $content.Replace('text-blue-50', 'text-emerald-50')

    # Borders
    $content = $content.Replace('border-blue-600', 'border-emerald-600')
    $content = $content.Replace('border-blue-500', 'border-emerald-500')
    $content = $content.Replace('border-blue-400', 'border-emerald-400')
    $content = $content.Replace('border-blue-300', 'border-emerald-300')
    $content = $content.Replace('border-blue-200', 'border-emerald-200')
    $content = $content.Replace('border-blue-100', 'border-emerald-100')
    $content = $content.Replace('border-blue-50', 'border-emerald-50')

    # Shadows
    $content = $content.Replace('shadow-blue-600', 'shadow-emerald-600')
    $content = $content.Replace('shadow-blue-500', 'shadow-emerald-500')
    $content = $content.Replace('shadow-blue-200', 'shadow-emerald-200')

    # Rings
    $content = $content.Replace('ring-blue-500', 'ring-emerald-500')
    $content = $content.Replace('ring-blue-600', 'ring-emerald-600')

    # Custom CSS vars in clock.blade.php
    $content = $content.Replace('--navy: #1e3a8a;', '--navy: #111827;')
    $content = $content.Replace('--navy-mid: #1e40af;', '--navy-mid: #1f2937;')
    $content = $content.Replace('--navy-light: #2563eb;', '--navy-light: #059669;')
    $content = $content.Replace('--cyan: #3b82f6;', '--cyan: #10b981;')

    Set-Content -Path $file.FullName -Value $content
}
