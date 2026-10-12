param([ValidateSet('bootstrap','regression','performance','controller','excel','capacity','migration','sidebar','acceptance','mutation','all')][string]$Test='all')
$ErrorActionPreference='Stop'
# Read the local DB container credential in memory, without logging or persisting it.
$containerInfo=docker inspect metalsindo_db | ConvertFrom-Json
if ($LASTEXITCODE -ne 0) { throw 'Cannot inspect local Docker DB.' }
$passwordEntry=@($containerInfo.Config.Env | Where-Object { $_.StartsWith('MYSQL_ROOT_PASSWORD=') })
if ($passwordEntry.Count -ne 1) { throw 'Expected local Docker root credential.' }
$localTestPassword=$passwordEntry[0].Substring('MYSQL_ROOT_PASSWORD='.Length)
$tests=if($Test -eq 'all') { @('bootstrap','regression','performance','controller','excel','migration','sidebar','acceptance','mutation') } else { @($Test) }
foreach($name in $tests) {
    docker exec -e REPORT_TEST_HOST=metalsindo_db -e REPORT_TEST_DATABASE=metalsindo_dev -e REPORT_TEST_USER=root -e "REPORT_TEST_PASSWORD=$localTestPassword" -w /var/www/metalsindo_2 metalsindo_web php "tests/report_incoming/$name.php"
    if($LASTEXITCODE -ne 0) { throw "Failed report test: $name" }
}
