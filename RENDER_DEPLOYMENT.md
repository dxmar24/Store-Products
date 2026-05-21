# Render Deployment Guide

This project now runs as a PHP 8.3 Docker service with Composer, the MongoDB PHP extension, and the official MongoDB PHP library.

## Required Environment Variables

Set these in Render:

```text
MONGODB_DATABASE=store_products_db
MONGODB_URI=mongodb+srv://USER:PASSWORD@CLUSTER.mongodb.net/?retryWrites=true&w=majority
```

Do not commit the real MongoDB URI to GitHub.

## Deploy

1. Push this project to GitHub.
2. In Render, create a Web Service or Blueprint from the repository.
3. Use Docker as the environment.
4. Add `MONGODB_URI` and `MONGODB_DATABASE` in the Render environment tab.
5. Deploy.

Render will build the Docker image, install PHP dependencies, and expose the app at:

```text
https://YOUR-SERVICE-NAME.onrender.com/
```

Health check:

```text
https://YOUR-SERVICE-NAME.onrender.com/api/health
```

## MongoDB Atlas

For a class demo, allow Render to connect from Atlas Network Access. `0.0.0.0/0` works for demos, but production should restrict access.
