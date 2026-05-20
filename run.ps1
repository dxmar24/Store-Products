if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "Created .env from .env.example. Edit .env with your MongoDB Atlas URI, then run this script again."
    exit 1
}

mvn jetty:run
