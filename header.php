<!Doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title><?php echo $pageTitle; ?></title>
        <meta name="description" content="University Management system">
		<meta name="author" content="Md Abul Kalam">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Place favicon.ico in the root directory -->

        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
        <link rel="stylesheet" href="fonts/stylesheet.css">
        <link rel="stylesheet" href="css/main.css">
        <script src="js/vendor/modernizr-2.8.3.min.js"></script>
        <style>
            .accordion-menu { list-style:none; padding:0; margin:0; }
            .accordion-menu .acc-item { margin-bottom:6px; }
            .accordion-menu .acc-toggle {
                display:flex; align-items:center; justify-content:space-between;
                cursor:pointer; user-select:none;
            }
            .accordion-menu .acc-caret { transition: transform 0.2s ease; font-size:13px; }
            .accordion-menu .acc-item.open > .acc-toggle .acc-caret { transform: rotate(180deg); }
            .accordion-menu .acc-panel {
                list-style:none; margin:0; padding:0;
                max-height:0; overflow:hidden;
                transition: max-height 0.25s ease;
            }
            .accordion-menu .acc-item.open > .acc-panel { max-height:300px; }
        </style>
    </head>
    <body>
        <!--[if lt IE 8]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->

        <!-- Add your site or application content here -->
        <header class="container header_area fix" style="background-color: skyblue">
			<div id="sticker" style="background-color: skyblue">
				<div class="head">
					<a href="#"><div class="logo fix">
						<!-- <img src="img/logo.png" alt="" /> -->
					</div></a></div>
					<div style="color: white;margin-left: 20px">
						<h2>University Management System</h2>
					</div>
				</div>
				<div class="menu fix">
					<div class="dateshow fix"><p><?php echo "Date : ".date("d M Y"); ?></p></div>
				<!--	<ul>
						<li><a href="#"><i class="fa fa-home" aria-hidden="true"></i> Home</a></li>
						
						<li><a href="#"><i class="fa fa-user" aria-hidden="true"></i> User</a></li>
					</ul>
				-->
				</div>
			</div>
		</header>
		<div class="maincontent container fix">
			<div id="stickerside" style="background-color: skyblue">
				<div class="sidebar fix" style="background-color: skyblue">
						<ul class="accordion-menu" style="margin-right: 20px;">
							<li class="acc-item">
								<span class="spcl acc-toggle"><i class="fa fa-server" aria-hidden="true"></i> Admin Login <i class="fa fa-chevron-down acc-caret" aria-hidden="true"></i></span>
								<ul class="acc-panel">
									<li><a href="index.php"><i class="fa fa-sign-in" aria-hidden="true"></i> Login</a></li>
								</ul>
							</li>

							<li class="acc-item">
								<span class="spcl acc-toggle"><i class="fa fa-male" aria-hidden="true"></i> Faculty Login <i class="fa fa-chevron-down acc-caret" aria-hidden="true"></i></span>
								<ul class="acc-panel">
									<li><a href="facultylogin.php"><i class="fa fa-sign-in" aria-hidden="true"></i> Login</a></li>
									<li><a href="fct_single_profile.php"><i class="fa fa-user" aria-hidden="true"></i> Profile</a></li>
									<li><a href="class_att_fc.php"><i class="fa fa-database" aria-hidden="true"></i> Class Attendance</a></li>
									<li><a href="fct_results.php"><i class="fa fa-outdent" aria-hidden="true"></i> Results</a></li>
								</ul>
							</li>

							<li class="acc-item">
								<span class="spcl acc-toggle"><i class="fa fa-graduation-cap" aria-hidden="true"></i> Student Login <i class="fa fa-chevron-down acc-caret" aria-hidden="true"></i></span>
								<ul class="acc-panel">
									<li><a href="st_login.php"><i class="fa fa-sign-in" aria-hidden="true"></i> Login</a></li>
									<li><a href="st_reg.php"><i class="fa fa-user-plus" aria-hidden="true"></i> Register</a></li>
									<li><a href="st_profile.php"><i class="fa fa-user" aria-hidden="true"></i> Profile</a></li>
									<li><a href="view_single_result.php"><i class="fa fa-outdent" aria-hidden="true"></i> Result</a></li>
								</ul>
							</li>
						</ul>

					</div>
					<script>
						document.querySelectorAll('.accordion-menu .acc-toggle').forEach(function(toggle){
							toggle.addEventListener('click', function(){
								var item = toggle.parentElement;
								var wasOpen = item.classList.contains('open');
								document.querySelectorAll('.accordion-menu .acc-item').forEach(function(i){
									i.classList.remove('open');
								});
								if(!wasOpen){ item.classList.add('open'); }
							});
						});
					</script>
				</div>
				<div class="content fix">