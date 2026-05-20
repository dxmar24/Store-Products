# Store Products

Java web application built with JSP, Vue 3, MVC servlets, and MongoDB Atlas through the Morphia ODM.

## Architecture

- Model: `Product` entity mapped to MongoDB with Morphia.
- View: JSP page with a Vue 3 inventory interface.
- Controller: Jakarta Servlet controllers for the page and JSON API.
- Service: product validation and business rules.
- Repository: MongoDB Atlas persistence through Morphia.

## MongoDB Atlas Configuration

Copy the example file:

```powershell
Copy-Item .env.example .env
```

Then edit `.env`:

```text
MONGODB_URI=mongodb+srv://USER:PASSWORD@CLUSTER.mongodb.net/?retryWrites=true&w=majority
MONGODB_DATABASE=store_products_db
```

Environment variables also work. If both exist, environment variables take priority.

## Run Locally

```powershell
mvn clean package
mvn jetty:run
```

Or use:

```powershell
.\run.ps1
```

Open:

```text
http://localhost:8080/store-products
```

## API

- `GET /store-products/api/products`
- `POST /store-products/api/products`
- `PUT /store-products/api/products/{id}`
- `DELETE /store-products/api/products/{id}`

## Deploy To Render

This project includes:

- `Dockerfile`: builds the WAR and runs it with Tomcat.
- `render.yaml`: Render Blueprint configuration.
- `.dockerignore`: keeps `.env` and build files out of the Docker image.

Steps:

1. Push the project to GitHub.
2. In Render, create a new Blueprint or Web Service from the repository.
3. Choose Docker as the environment if Render asks.
4. Add the environment variables in Render. Use the same private MongoDB URI that is stored locally in `.env`; do not commit `.env` to GitHub.

```text
MONGODB_DATABASE=store_products_db
MONGODB_URI=mongodb://USER:PASSWORD@HOST-1:27017,HOST-2:27017,HOST-3:27017/?ssl=true&replicaSet=REPLICA_SET&authSource=admin&retryWrites=true&w=majority
```

5. Deploy.

Important for MongoDB Atlas:

- In Atlas, go to Network Access and allow Render to connect.
- For a class demo, you can allow `0.0.0.0/0`.
- For a real production app, restrict access to trusted IPs only.

On Render the application opens at the root URL:

```text
https://YOUR-RENDER-SERVICE.onrender.com/
```

The table view is:

```text
https://YOUR-RENDER-SERVICE.onrender.com/table
```
