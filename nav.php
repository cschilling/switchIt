<!-- Fixed navbar -->
<div class="navbar navbar-fixed-top">
  <div class="container">
	<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".nav-collapse">
	  <span class="icon-bar"></span>
	  <span class="icon-bar"></span>
	  <span class="icon-bar"></span>
	</button>
	<a class="navbar-brand" href="#"><?php echo $C_title; ?></a>
	<div class="nav-collapse collapse">
	  <ul class="nav navbar-nav">
		<li class="active"><a href="#">Home</a></li>
		<li><a href="#about">About</a></li>
		<li><a href="#contact">Contact</a></li>
		<li class="dropdown">
		  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
		  <ul class="dropdown-menu">
			<li><a href="#">Action</a></li>
			<li><a href="#">Another action</a></li>
			<li><a href="#">Something else here</a></li>
			<li class="divider"></li>
			<li class="nav-header">Nav header</li>
			<li><a href="#">Separated link</a></li>
			<li><a href="#">One more separated link</a></li>
		  </ul>
		</li>
	  </ul>
	  <ul class="nav navbar-nav pull-right">
		<li><a href="/examples/navbar/">Default</a></li>
		<li><a href="/examples/navbar-static-top/">Static top</a></li>
		<li class="active"><a href="/examples/navbar-fixed-top/">Fixed top</a></li>
	  </ul>
	</div><!--/.nav-collapse -->
  </div>
</div>