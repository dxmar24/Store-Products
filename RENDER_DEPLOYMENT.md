# Render Deployment Guide

This project is ready to deploy on Render using Docker.

## Files Added For Render

- `Dockerfile`: builds the Maven WAR and runs it with Tomcat.
- `render.yaml`: optional Render Blueprint configuration.
- `.dockerignore`: prevents local files like `.env` and `target/` from being copied into the Docker image.

## Step 1: Prepare MongoDB Atlas

1. Open MongoDB Atlas.
2. Go to `Network Access`.
3. Add an IP address.
4. For a class/demo deployment, use:

```text
0.0.0.0/0
```

This allows Render to connect to Atlas from the cloud.

## Step 2: Upload The Project To GitHub

Create a GitHub repository and upload this project.

Do not upload `.env`. It is already ignored by `.gitignore`.

If using Git from the terminal:

```powershell
git init
git add .
git commit -m "Deploy store products app"
git branch -M main
git remote add origin https://github.com/YOUR_USER/YOUR_REPOSITORY.git
git push -u origin main
```

## Step 3: Create The Render Service

1. Open Render.
2. Click `New`.
3. Choose `Web Service`.
4. Connect the GitHub repository.
5. Select Docker as the environment.
6. Use the default branch, usually `main`.
7. Click `Create Web Service`.

Render will read the `Dockerfile`, build the WAR, and run it with Tomcat.

## Step 4: Add Environment Variables In Render

In the Render service, open `Environment` and add:

```text
MONGODB_DATABASE=store_products_db
MONGODB_URI=the same MongoDB URI from your local .env file
```

Keep `MONGODB_URI` private. Do not write the real username and password in GitHub files.

## Step 5: Open The Cloud URL

After Render finishes the deployment, open:

```text
https://YOUR-SERVICE-NAME.onrender.com/
```

The table page is:

```text
https://YOUR-SERVICE-NAME.onrender.com/table
```

## Local Verification

The project was verified locally with:

```powershell
mvn clean package
```

The build creates:

```text
target/store-products.war
```
