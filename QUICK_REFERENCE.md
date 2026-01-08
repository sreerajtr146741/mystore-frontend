# 🎯 Quick Reference Guide - Buyorix

## 🚀 Common Commands

### **Deploy Changes**
```bash
git add .
git commit -m "Description of changes"
git push origin main
```

### **Check Deployment Status**
Visit: https://dashboard.render.com

### **View Live Site**
https://buyorix.onrender.com

---

## 📝 Adding New Routes

### **Step 1: Create View File**
Create a new PHP file in `mystorefrontend/` directory:

```php
// Example: mystorefrontend/services.php
@extends('layouts.app')

@section('content')
    <h1>Our Services</h1>
    <!-- Your content here -->
@endsection
```

### **Step 2: Access the Route**
The route is automatically available at:
- File: `mystorefrontend/services.php`
- URL: `https://buyorix.onrender.com/services`

### **Step 3: For Directory-Based Routes**
```
mystorefrontend/
  └── blog/
      └── index.php  → Access at /blog
```

---

## 🛍️ Adding New Products

Edit `index.php` and add to the `$data['products']` array:

```php
(object)[
    'id' => 7,
    'name' => 'Product Name',
    'description' => 'Product description',
    'price' => 1999,
    'stock' => 20,
    'category' => 'Electronics',
    'image' => 'https://images.unsplash.com/photo-xxx',
    'created_at' => Carbon::now()->subDays(1)
],
```

---

## 🎨 Customizing Styles

### **Global Styles**
Edit: `mystorefrontend/layouts/app.php` or `mystorefrontend/layouts/master.php`

### **Page-Specific Styles**
Add `<style>` tags in individual view files

---

## 🔧 Common Issues & Fixes

### **404 Error on New Route**
1. Check file exists in `mystorefrontend/`
2. Clear browser cache
3. Check file permissions (755 for directories, 644 for files)

### **Changes Not Showing**
1. Hard refresh browser (Ctrl + Shift + R)
2. Clear Blade cache: Delete files in `cache/` directory
3. Redeploy on Render

### **Deployment Failed**
1. Check Render logs
2. Verify `composer.json` is valid
3. Test Docker build locally:
   ```bash
   docker build -t buyorix-test .
   ```

---

## 📂 Important Files

| File | Purpose |
|------|---------|
| `index.php` | Main router & data source |
| `Dockerfile` | Docker build configuration |
| `render.yaml` | Render deployment settings |
| `.htaccess` | URL rewriting rules |
| `composer.json` | PHP dependencies |
| `mystorefrontend/layouts/app.php` | Main layout template |

---

## 🌐 Environment

- **Platform:** Render
- **PHP Version:** 8.2
- **Web Server:** Apache 2.4
- **Deployment:** Automatic on git push

---

## 📞 Quick Links

- **Live Site:** https://buyorix.onrender.com
- **Render Dashboard:** https://dashboard.render.com
- **GitHub Repo:** [Your repository URL]

---

*Keep this file handy for quick reference!*
