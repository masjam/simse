Add-Type -AssemblyName System.Drawing
New-Item -ItemType Directory -Force -Path public/icons | Out-Null

$sizes = @(192, 512)
foreach ($s in $sizes) {
    $bmp = New-Object System.Drawing.Bitmap $s, $s
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
    $brushBg = New-Object System.Drawing.SolidBrush ([System.Drawing.Color]::FromArgb(15, 118, 110))
    $g.FillEllipse($brushBg, 4, 4, ($s - 8), ($s - 8))

    $fontSize = [float]($s / 5)
    $font = New-Object System.Drawing.Font 'Arial', $fontSize, ([System.Drawing.FontStyle]::Bold)
    $brushFg = New-Object System.Drawing.SolidBrush ([System.Drawing.Color]::White)
    $format = New-Object System.Drawing.StringFormat
    $format.Alignment = [System.Drawing.StringAlignment]::Center
    $format.LineAlignment = [System.Drawing.StringAlignment]::Center

    $rect = New-Object System.Drawing.RectangleF 0, 0, $s, $s
    $g.DrawString('SDAM', $font, $brushFg, $rect, $format)

    $outPath = "public/icons/icon-${s}x${s}.png"
    $bmp.Save($outPath, [System.Drawing.Imaging.ImageFormat]::Png)

    $g.Dispose()
    $bmp.Dispose()
}

Write-Host "PWA icons generated successfully."

