# Linked Product – Magento 2 Extension

A Magento 2 module that links products sharing similar styles while differing in attributes such as size, color, or material.
This improves product discoverability and enhances the shopping experience.

---

## 📌 Features

* Link similar products by attributes (e.g., color, size, material).
* Show linked products on:

    * Product Listing Page
    * Product View Page
* Display attribute text (e.g., "Color: Red").
* Display stock status for each linked product.
* Option to show only the **count** of available linked products instead of listing them.
* Easy-to-configure backend settings.

---

## ⚙️ Configuration

Navigate to:
**Stores → Configuration → Sutunam → Linked Product**

### 1. General Settings

| Setting                                       | Description                                               |
| --------------------------------------------- | --------------------------------------------------------- |
| **Enable**                                    | Enable/Disable the module                                 |
| **Show on product listing**                   | Display linked products on category/product listing pages |
| **Show on product view**                      | Display linked products on product detail pages           |
| **Show attribute text**                       | Show selected attribute labels with each linked product   |
| **Show available linked products count**      | Replace linked product list with product count text       |
| **Show stock status**                         | Display each linked product’s stock availability          |
| **Show stock status text on product listing** | Show stock status on listing pages                        |
| **Show stock status text on product view**    | Show stock status on product pages                        |

---

### 2. Attribute Mapping

Path:
**Sutunam → Linked Product → Mapping**

Enables customization of the text shown when displaying the number of linked products

**Example:**

| Attribute Code | Singular             | Plural                        |
| -------------- | -------------------- | ----------------------------- |
| `color`        | Available in 1 color | Available in {{count}} colors |

### 3. Product Linked

**Product → Related Products, Up-Sells, Cross-Sells and Linked → Linked Products**

---

## 📦 Installation

### Install via Composer

1. Add the Sutunam Composer repository:

```json
"repositories": {
    "sutunam": {
        "type": "composer",
        "url": "https://composer.sutunam.com/m2/"
    }
}
```

2. Require the module:

```bash
composer require sutunam/linked-product
```

3. Enable and upgrade the module:

```bash
bin/magento module:enable Sutunam_LinkedProduct
bin/magento setup:upgrade
bin/magento cache:flush
```

4. For production mode:

```bash
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
```

---

## 📝 Changelog

### 1.0.1

* Updated documentation.

### 1.0.0

* Initial module release.

---

## ✔️ Improvements

* Structured for readability and clarity.
* Features explained in simple language.
* Tables used for configuration settings and mapping.
* Easy-to-follow installation instructions.
