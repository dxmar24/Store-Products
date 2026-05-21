# Store Products

PHP 8.3 inventory app connected to MongoDB Atlas. It includes a browser interface, JSON API, server-side validation, client-side validation, and a Docker setup ready for Render.

## Stack

- PHP 8.3
- Composer
- MongoDB PHP extension
- Official `mongodb/mongodb` PHP library
- MongoDB Atlas
- Vanilla JavaScript, HTML, and CSS

## Environment

Create `.env` from the example:

```powershell
Copy-Item .env.example .env
```

Then edit `.env`:

```text
MONGODB_URI=mongodb+srv://USER:PASSWORD@CLUSTER.mongodb.net/?retryWrites=true&w=majority
MONGODB_DATABASE=store_products_db
```

Environment variables take priority over `.env`.

## Run Locally

Docker is the local runtime for this project. The image installs PHP, Composer, and the MongoDB PHP extension:

```powershell
.\run.ps1
```

Open:

```text
http://localhost:8080
```

## API

- `GET /api/health`
- `GET /api/products`
- `POST /api/products`
- `PUT /api/products/{id}`
- `DELETE /api/products/{id}`

Product fields:

- `sku`: required, uppercase letters, numbers, and hyphens, max 30 chars
- `name`: required, max 80 chars
- `category`: required, max 50 chars
- `price`: required, greater than 0, max 999999
- `stock`: required integer, greater than 0, max 999999
- `active`: boolean

## Deploy To Render

The included `Dockerfile` and `render.yaml` are ready for Render. Add `MONGODB_URI` as a private environment variable in Render before deploying.
