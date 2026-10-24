# Deployment Packaging Script for Alwaysdata
$ErrorActionPreference = "Stop"

$zipFile = "alwaysdata_deploy.zip"
$stageDir = "$env:TEMP\alwaysdata_stage"

if (Test-Path $zipFile) {
    Remove-Item $zipFile -Force
}
if (Test-Path $stageDir) {
    Remove-Item $stageDir -Recurse -Force
}

New-Item -ItemType Directory -Path $stageDir | Out-Null

Write-Host "Copying project files to temporary staging..."
$exclude = @('vendor', 'node_modules', '.git', 'alwaysdata_deploy.zip', '.env', 'package_for_deploy.ps1')

Get-ChildItem -Path . | Where-Object {
    $exclude -notcontains $_.Name
} | ForEach-Object {
    Copy-Item -Path $_.FullName -Destination $stageDir -Recurse -Force
}

# Create a clean production .env.production template in the package
Copy-Item -Path ".env.example" -Destination "$stageDir\.env.example"

Write-Host "Compressing deployment package..."
Compress-Archive -Path "$stageDir\*" -DestinationPath $zipFile -CompressionLevel Optimal

# Cleanup stage
Remove-Item $stageDir -Recurse -Force

$sizeMB = [math]::Round((Get-Item $zipFile).Length / 1MB, 2)
Write-Host "Successfully generated $zipFile ($sizeMB MB) ready for Alwaysdata upload!"
