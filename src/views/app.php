<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Products</title>
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
<main class="shell">
    <header class="topbar">
        <div>
            <p class="eyebrow">PHP + MongoDB Atlas</p>
            <h1>Store Products</h1>
        </div>
        <div class="top-actions">
            <button class="secondary" id="refreshButton" type="button">Refresh</button>
            <button class="primary" id="newButton" type="button">New product</button>
        </div>
    </header>

    <section class="notice" id="notice" hidden></section>

    <section class="summary-grid" aria-label="Inventory summary">
        <article class="summary-card">
            <span>Products</span>
            <strong id="summaryProducts">0</strong>
        </article>
        <article class="summary-card">
            <span>Total units</span>
            <strong id="summaryUnits">0</strong>
        </article>
        <article class="summary-card highlight">
            <span>Inventory total</span>
            <strong id="summaryValue">$0.00</strong>
        </article>
    </section>

    <section class="layout">
        <form class="panel editor" id="productForm" novalidate>
            <div class="section-title">
                <h2 id="formTitle">Create product</h2>
                <span id="modeBadge">New</span>
            </div>

            <input type="hidden" id="productId">

            <label>
                SKU
                <input id="sku" name="sku" placeholder="SKU-1001" maxlength="30" pattern="[A-Za-z0-9-]+" autocomplete="off" required>
                <small data-error-for="sku"></small>
            </label>

            <label>
                Product name
                <input id="name" name="name" placeholder="Wireless keyboard" maxlength="80" autocomplete="off" required>
                <small data-error-for="name"></small>
            </label>

            <label>
                Category
                <input id="category" name="category" placeholder="Accessories" maxlength="50" autocomplete="off" required>
                <small data-error-for="category"></small>
            </label>

            <div class="fields">
                <label>
                    Price
                    <input id="price" name="price" type="number" min="0.01" max="999999" step="0.01" placeholder="49.99" required>
                    <small data-error-for="price"></small>
                </label>

                <label>
                    Quantity
                    <input id="stock" name="stock" type="number" min="1" max="999999" step="1" placeholder="10" required>
                    <small data-error-for="stock"></small>
                </label>
            </div>

            <label class="toggle">
                <input id="active" name="active" type="checkbox" checked>
                Active product
            </label>
            <small data-error-for="active"></small>

            <div class="actions">
                <button class="primary" id="submitButton" type="submit">Create</button>
                <button class="secondary" id="clearButton" type="button">Clear</button>
            </div>
        </form>

        <section class="content-stack">
            <section class="panel details-panel" id="detailsPanel">
                <div class="section-title">
                    <h2>Product details</h2>
                    <span id="detailsStatus">No selection</span>
                </div>
                <div class="detail-empty" id="detailEmpty">
                    Select a product from the inventory table.
                </div>
                <div class="detail-content" id="detailContent" hidden>
                    <div>
                        <p class="eyebrow" id="detailSku"></p>
                        <h3 id="detailName"></h3>
                        <p id="detailCategory"></p>
                    </div>
                    <dl class="detail-grid">
                        <div>
                            <dt>Base price</dt>
                            <dd id="detailPrice"></dd>
                        </div>
                        <div>
                            <dt>Quantity</dt>
                            <dd id="detailStock"></dd>
                        </div>
                        <div>
                            <dt>Total value</dt>
                            <dd id="detailTotal"></dd>
                        </div>
                        <div>
                            <dt>Created</dt>
                            <dd id="detailCreated"></dd>
                        </div>
                        <div>
                            <dt>Updated</dt>
                            <dd id="detailUpdated"></dd>
                        </div>
                    </dl>
                    <div class="actions">
                        <button class="secondary" id="detailEditButton" type="button">Edit selected</button>
                        <button class="secondary" id="detailClearButton" type="button">Clear selection</button>
                    </div>
                </div>
            </section>

            <section class="panel inventory">
                <div class="section-title">
                    <h2>Inventory</h2>
                    <span id="countBadge">0 products</span>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody id="productRows">
                        <tr>
                            <td class="empty-cell" colspan="9">Loading products...</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </section>
    </section>
</main>

<template id="rowTemplate">
    <tr>
        <td data-cell="sku"></td>
        <td data-cell="name"></td>
        <td data-cell="category"></td>
        <td data-cell="price"></td>
        <td data-cell="stock"></td>
        <td data-cell="total" class="strong"></td>
        <td><span data-cell="status" class="status"></span></td>
        <td data-cell="createdAt"></td>
        <td>
            <div class="row-actions">
                <button class="secondary small" type="button" data-action="view">View</button>
                <button class="secondary small" type="button" data-action="edit">Edit</button>
                <button class="danger small" type="button" data-action="delete">Delete</button>
            </div>
        </td>
    </tr>
</template>

<script src="/assets/app.js"></script>
</body>
</html>
