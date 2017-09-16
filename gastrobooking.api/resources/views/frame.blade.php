<html>
<head>
	<title></title>
	<style type="text/css">
	* {
	    -webkit-box-sizing: border-box;
	    -moz-box-sizing: border-box;
	    box-sizing: border-box;
	}

	html {
		font-size: 16px;
	}

	body {
		margin: 20px;
	}

	.container-fluid {
		padding: 0 15px;
	}

	.row:before, .row:after {
		display: table;
    	content: " ";
	}

	.row:after {
		clear: both;
	}

	.text-center {
		text-align: center;
	}

	.item {
	    padding: 20px;
	    margin: 20px -15px;
	}

	.item .image {
		width: 100%;
    	height: auto;
	}

	.item .title {
		margin: 10px 0;
	}

	.col-md-1 {
		width: 8.33333333333%;
		float: left;
		padding: 0 15px;
	}
	
	.col-md-2 {
		width: 16.6666666667%;
		float: left;
			padding: 0 15px;
		}
			.col-md-3 {
			width: 25%;
			float: left;
			padding: 0 15px;
		}
			.col-md-4 {
			width: 33.3333333333%;
			float: left;
			padding: 0 15px;
		}
			.col-md-5 {
			width: 41.6666666667%;
			float: left;
			padding: 0 15px;
		}
			.col-md-6 {
			width: 50%;
			float: left;
			padding: 0 15px;
		}
			.col-md-7 {
			width: 58.3333333333%;
			float: left;
			padding: 0 15px;
		}
	.col-md-8 {
			width: 66.6666666667%;
			float: left;
			padding: 0 15px;
		}
	.col-md-9 {
			width: 75%;
			float: left;
			padding: 0 15px;
		}
	.col-md-10 {
			width: 83.3333333333%;
			float: left;
			padding: 0 15px;
		}
	.col-md-11 {
			width: 91.6666666667%;
			float: left;
			padding: 0 15px;
		}
	.col-md-12 {
			width: 100%;
			float: left;
			padding: 0 15px;
		}

	<?php 
		$styles = [
			'bg' => ['body', 'background-color', '#fafafa'],
			'fontname' => ['*', 'font-family', 'Consolas'],
			'fontcolor' => ['*', 'color', '#343434'],
			'fontsize' => ['*', 'font-size', '15px'],
			'photo' => ['.item .image', 'display', 'block'],

			'itembg' => ['.item', 'background-color', '#fff'],
			'itemborder' => ['.item', 'border', '1px solid #ddd']
		];
	?>

	@foreach($styles as $param => $data)
	<?php list($el, $prop, $default) = $data ?>

	{{ $el }} {
		{{ $prop }}: {{ Request::get($param, $default) }};
	}
	@endforeach
	</style>
</head>

<body>
<div class="container-fluid">
	@foreach($items as $item)
		<div class="row item">
			<div class="col-md-1">
				<img src="http://api.gastro-booking.cz/uploads/items/{{ $item->photo }}" class="image" />
			</div>

			<div class="col-md-5">
				<h4 class="title">{{ $item->name }}</h4>
				<p class="comment">{!! $item->comment !!}</p>
			</div>
			
			<div class="col-md-1">
				<h4 class="title">{{ $item->preifx }}</h4>
			</div>
			
			<div class="col-md-2">
				<h3 class="price">{{ round($item->price) }} {{ $item->currency }}</h3>
			</div>
	                            
			<div class="col-md-3 text-center">
				<button class="button"></button>
			</div>
		</div>
	@endforeach
</div>
</body>
</html>