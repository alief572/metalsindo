param([ValidateSet('apply','rollback')][string]$Action='apply')
$ErrorActionPreference='Stop'
$containerInfo=docker inspect metalsindo_db | ConvertFrom-Json
if($LASTEXITCODE -ne 0) { throw 'Cannot inspect local Docker DB.' }
$passwordEntry=@($containerInfo.Config.Env | Where-Object { $_.StartsWith('MYSQL_ROOT_PASSWORD=') })
if($passwordEntry.Count -ne 1) { throw 'Expected local Docker root credential.' }
$localInstallPassword=$passwordEntry[0].Substring('MYSQL_ROOT_PASSWORD='.Length)
docker exec -e REPORT_INSTALL_HOST=metalsindo_db -e REPORT_INSTALL_DATABASE=metalsindo_dev -e REPORT_INSTALL_USER=root -e "REPORT_INSTALL_PASSWORD=$localInstallPassword" -w /var/www/metalsindo_2 metalsindo_web php scripts/register_report_incoming.php $Action
if($LASTEXITCODE -ne 0) { throw 'Local Report Incoming registration failed.' }
