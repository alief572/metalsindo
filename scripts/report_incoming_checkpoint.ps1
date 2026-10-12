param(
    [ValidateSet('record','verify')][string]$Mode = 'verify',
    [Parameter(Mandatory=$true)][ValidateRange(1,6)][int]$Phase,
    [string]$Summary,
    [string]$Checks,
    [string]$Remaining,
    [string[]]$Files
)
$ErrorActionPreference = 'Stop'
$workspace = Split-Path $PSScriptRoot -Parent
$checkpointDirectory = Join-Path $workspace 'md/report_incoming/checkpoints'
$manifestPath = Join-Path $checkpointDirectory ('phase-{0}.json' -f $Phase)
$notePath = Join-Path $checkpointDirectory ('phase-{0}.md' -f $Phase)
if ($Mode -eq 'record') {
    if (!$Files -or !$Summary -or !$Checks) { throw 'Record requires Files, Summary and Checks.' }
    New-Item -ItemType Directory -Path $checkpointDirectory -Force | Out-Null
    $entries = @($Files | ForEach-Object {
        $resolved = [IO.Path]::GetFullPath((Join-Path $workspace $_))
        if (!$resolved.StartsWith($workspace + [IO.Path]::DirectorySeparatorChar, [StringComparison]::OrdinalIgnoreCase)) { throw 'File outside workspace.' }
        @{ path = $_; sha256 = (Get-FileHash -LiteralPath $resolved -Algorithm SHA256).Hash }
    })
    @{ phase=$Phase; files=$entries } | ConvertTo-Json -Depth 4 | Set-Content -LiteralPath $manifestPath -Encoding UTF8
    @("# Checkpoint Fase $Phase",'', 'Status: selesai dan terverifikasi.', '', '## Perubahan / alasan', $Summary,
        '', '## Pemeriksaan', $Checks, '', '## Risiko / pekerjaan berikutnya', $Remaining,
        '', '## File', ($Files | ForEach-Object { '- ' + $_ }), '',
        'Manifest SHA256 tersimpan pada JSON fase yang sama. Verifikasi checkpoint sebelum memulai fase berikutnya.') |
        Set-Content -LiteralPath $notePath -Encoding UTF8
}
$manifest = Get-Content -LiteralPath $manifestPath -Raw | ConvertFrom-Json
foreach ($entry in $manifest.files) {
    $actual = (Get-FileHash -LiteralPath (Join-Path $workspace $entry.path) -Algorithm SHA256).Hash
    if ($actual -ne $entry.sha256) { throw "Checkpoint changed: $($entry.path)" }
}
Get-Content -LiteralPath $notePath
Write-Output "PASS checkpoint phase $Phase ($($manifest.files.Count) file hashes)"

