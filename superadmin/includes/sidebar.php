 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Inventory Management - Super Admin</title>
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
     <link rel="stylesheet" href="css/style.css">
     <style>
         .sidebar-menu a {
             display: block;
             padding: 10px;
             color: #fff;
             text-decoration: none;
             transition: background 0.3s;
         }

         .sidebar-menu a:hover {
             background: #5a5a5a;
             /* Darken on hover */
         }

         .sidebar-menu a.active {
             background: #007bff;
             /* Highlight the active page */
             font-weight: bold;
         }
     </style>
 </head>

 <body>
     <?php
        $current_page = basename($_SERVER['PHP_SELF']); // Get the current file name
        ?>

     <!-- Sidebar -->
     <div class="sidebar" id="sidebar">
         <div class="sidebar-header">
             <h4>Pilar Inventory Management System</h4>
         </div>
         <ul class="sidebar-menu">
             <li><a href="super_admin_dashboard.php" class="<?= ($current_page == 'super_admin_dashboard.php') ? 'active' : '' ?>">
                     <i class="bi bi-speedometer2"></i> Dashboard</a></li>

             <li><a href="user_management.php" class="<?= ($current_page == 'user_management.php') ? 'active' : '' ?>">
                     <i class="bi bi-people"></i> User Management</a></li>

             <li><a href="inventory.php" class="<?= ($current_page == 'inventory.php') ? 'active' : '' ?>">
                     <i class="bi bi-box-seam"></i> Inventory</a></li>

             <li><a href="reports.php" class="<?= ($current_page == 'reports.php') ? 'active' : '' ?>">
                     <i class="bi bi-graph-up"></i> Reports</a></li>

             <li><a href="settings.php" class="<?= ($current_page == 'settings.php') ? 'active' : '' ?>">
                     <i class="bi bi-gear"></i> Settings</a></li>

             <li><a href="../logout.php">
                     <i class="bi bi-box-arrow-right"></i> Logout</a></li>
         </ul>
     </div>

 </body>

 </html>