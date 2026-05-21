$ErrorActionPreference = "Stop"

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    Write-Host "Docker is required to run this project locally. Install Docker Desktop and try again."
    exit 1
}

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "Created .env from .env.example. Edit .env with your MongoDB Atlas URI, then run this script again."
    exit 1
}

$image = "store-products-php"
$container = "store-products-php"

docker build -t $image .

$existing = docker ps -aq --filter "name=^/$container$"
if ($existing) {
    docker rm -f $container | Out-Null
}

docker run --rm --name $container --env-file .env -e PORT=8080 -p 8080:8080 $image
