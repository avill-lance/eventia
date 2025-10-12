<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin-css\admin-style.css">
</head>
<body>
  <div class="layout">
    <aside class="sidebar" id="sidebar">
      <div class="brand">
        <div class="brand-logo"></div>
        <h1>Admin</h1>
      </div>
      <nav class="nav" id="nav">
        <a href="#/dashboard" data-route="dashboard" class="active">📊 Dashboard</a>
        <a href="#/packages" data-route="packages">📦 Packages</a>
        <a href="#/services" data-route="services">🛠️ Services</a>
        <a href="#/blog" data-route="blog">📝 Blog</a>
        <a href="#/bookings" data-route="bookings">📅 Package booking</a>
        <a href="#/products" data-route="products">🛍️ Shop manage product</a>
        <a href="#/inquiries" data-route="inquiries">❓ Inquiries</a>
        <a href="#/reviews" data-route="reviews">⭐ Clients review</a>
        <a href="#/settings" data-route="settings">⚙️ Settings</a>
      </nav>
    </aside>

    <section>
      <header class="topbar">
        <button class="btn menu-btn" id="toggleSidebar">☰</button>
        <div class="search">
          <span>🔎</span>
          <input id="globalSearch" placeholder="Search (name/description/email)…" />
        </div>
        <div class="right">Welcome, <span id="adminName">Admin</span></div>
      </header>