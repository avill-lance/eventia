
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f4f4f4;
    }
    header, aside, main {
      display: none; /* hidden until login */
    }
    /* Login Page */
    .login-container {
      display: flex;
      height: 100vh;
      justify-content: center;
      align-items: center;
      background: #2c3e50;
    }
    .login-box {
      background: #fff;
      padding: 2rem;
      border-radius: 10px;
      width: 300px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    .login-box h2 {
      margin-bottom: 1rem;
      text-align: center;
    }
    .login-box input {
      width: 100%;
      padding: 10px;
      margin: 8px 0;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    .login-box button {
      width: 100%;
      padding: 10px;
      background: #3498db;
      border: none;
      color: #fff;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
    }
    .login-box button:hover {
      background: #2980b9;
    }
    /* Dashboard Styles */
    header {
      background: #34495e;
      color: #fff;
      padding: 1rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    aside {
      position: fixed;
      top: 0;
      left: 0;
      width: 220px;
      height: 100%;
      background: #2c3e50;
      color: #fff;
      padding-top: 60px;
    }
    aside ul {
      list-style: none;
      padding: 0;
    }
    aside ul li {
      padding: 15px;
      cursor: pointer;
    }
    aside ul li:hover {
      background: #1abc9c;
    }
    main {
      margin-left: 220px;
      padding: 20px;
    }
  </style>
</head>
<body>

  <!-- Login Page -->
  <div class="login-container" id="login-page">
    <div class="login-box">
      <h2>Admin Login</h2>
      <input type="text" id="username" placeholder="Username" required>
      <input type="password" id="password" placeholder="Password" required>
      <button onclick="login()">Login</button>
    </div>
  </div>

  <!-- Dashboard -->
  <header id="header">
    <div>Admin Dashboard</div>
    <div><button onclick="logout()">Logout</button></div>
  </header>

  <aside id="sidebar">
    <ul>
      <li onclick="navigate('dashboard')">Dashboard</li>
      <li onclick="navigate('packages')">Packages</li>
      <li onclick="navigate('services')">Services</li>
      <li onclick="navigate('blog')">Blog</li>
      <li onclick="navigate('booking')">Package booking</li>
      <li onclick="navigate('shop')">Shop manage product</li>
      <li onclick="navigate('inquiries')">Inquiries</li>
      <li onclick="navigate('reviews')">Clients review</li>
      <li onclick="navigate('settings')">Settings</li>
    </ul>
  </aside>

  <main id="content">
    <h1>Welcome to the Dashboard</h1>
    <p>Select a section from the sidebar.</p>
  </main>

  <script>
    // Simple hardcoded admin login (you can expand this)
    const ADMIN_USER = "admin";
    const ADMIN_PASS = "1234";

    function login() {
      const user = document.getElementById("username").value;
      const pass = document.getElementById("password").value;
      if (user === ADMIN_USER && pass === ADMIN_PASS) {
        document.getElementById("login-page").style.display = "none";
        document.getElementById("header").style.display = "flex";
        document.getElementById("sidebar").style.display = "block";
        document.getElementById("content").style.display = "block";
      } else {
        alert("Invalid credentials!");
      }
    }

    function logout() {
      document.getElementById("login-page").style.display = "flex";
      document.getElementById("header").style.display = "none";
      document.getElementById("sidebar").style.display = "none";
      document.getElementById("content").style.display = "none";
      document.getElementById("username").value = "";
      document.getElementById("password").value = "";
    }

    function navigate(page) {
      const content = document.getElementById("content");
      switch(page) {
        case "packages":
          content.innerHTML = "<h2>Packages</h2><p>Manage your packages here.</p>";
          break;
        case "services":
          content.innerHTML = "<h2>Services</h2><p>Manage your services here.</p>";
          break;
        case "blog":
          content.innerHTML = "<h2>Blog</h2><p>Manage your blog posts here.</p>";
          break;
        case "booking":
          content.innerHTML = "<h2>Package Booking</h2><p>Manage bookings here.</p>";
          break;
        case "shop":
          content.innerHTML = "<h2>Shop Products</h2><p>Manage shop products here.</p>";
          break;
        case "inquiries":
          content.innerHTML = "<h2>Inquiries</h2><p>Check user inquiries here.</p>";
          break;
        case "reviews":
          content.innerHTML = "<h2>Clients Review</h2><p>See client reviews here.</p>";
          break;
        case "settings":
          content.innerHTML = "<h2>Settings</h2><p>Update admin settings here.</p>";
          break;
        default:
          content.innerHTML = "<h1>Welcome to the Dashboard</h1><p>Select a section from the sidebar.</p>";
      }
    }
  </script>
</body>
</html>
