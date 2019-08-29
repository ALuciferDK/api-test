<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport"
		content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta content="yes" name="apple-mobile-web-app-capable">
	<meta content="black" name="apple-mobile-web-app-status-bar-style">
	<meta content="telephone=no" name="format-detection">
	<meta content="email=no" name="format-detection">
	<title></title>
	<link rel="stylesheet" type="text/css" href="css/base.css" />
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<link rel="stylesheet" type="text/css" href="css/icon.css">
	<script src="js/adaptive.js"></script>
	<script src="js/jquery.min.js"></script>
	<script>
		// 设计图宽度
		window['adaptive'].desinWidth = 640;
		// body 字体大小
		window['adaptive'].baseFont = 18;
		// 显示最大宽度 
		window['adaptive'].maxWidth = 640;
		// 初始化
		window['adaptive'].init();
	</script>
</head>

<body>
	<div class="container">
		<div class="top">
			<span class="iconfont back">&#xe653;</span>
			<p>第三方DEMO应用</p>
			<span class="iconfont more">&#xe6fc;</span>
		</div>
		<ul>
			<li id="btn1">获取用户身份</li>
			<li id="btn2">获取用户敏感信息</li>
			<li id="btn3">获取单个用户信息<br><span>(含所有角色)</span></li>
			<li id="btn4">获取单个用户迁入迁出<br><span>支部记录</span></li>
			<li id="btn5">获取委员会成员</li>
			<li id="btn6">获取支部党员</li>
			<li id="btn7">获取党组织树结构</li>
			<li id="btn8">获取党组织列表</li>
			<li id="btn9">获取某组织下属党支部<br><span>列表</span></li>
		</ul>
		<!-- 遮罩层弹框 -->
		<div class="mask" id="mask">
			<div class="dialog" id="dialog">
				<div class="title">返回信息<i class="iconfont close" id="close">&#xeb6a;</i></div>
				<div class="main">
					<div id="content"></div>
				</div>
			</div>
		</div>
	</div>
</body>

</html>

<script>
	let baseUrl = "http://39.105.34.162";

	// window.onLoad = clear()

	// 用户信息按钮
	$("#btn1").on("touchend", function () {
		$("#mask").css({ "display": "block" })
		getUserInfo()
	})

	//用户敏感信息
	$("#btn2").on("touchend", function () {
		$("#mask").css({ "display": "block" })
		getUserSecret()
	})

	//单个用户信息
	$("#btn3").on("touchend", function () {
		$("#mask").css({ "display": "block" })
		getGroupSingeInfo()
	})

	//单个用户迁入迁出记录
	$("#btn4").on("touchend", function () {
		$("#mask").css({ "display": "block" })
		getUserInOrOut()
	})

	//获取委员会成员
	$("#btn5").on("touchend", function () {
		$("#mask").css({ "display": "block" })
		getCommitteeList()
	})

	//获取支部党员
	$("#btn6").on("touchend", function () {
		$("#mask").css({ "display": "block" })
		getListPeople()
	})

	//获取党组织树结构
	$("#btn7").on("touchend", function () {
		$("#mask").css({ "display": "block" })
		getGroupTree()
	})

	//获取党组织列表
	$("#btn8").on("touchend", function () {
		$("#mask").css({ "display": "block" })
		getGroupList()
	})

	//获取某组织下属党支部
	$("#btn9").on("touchend", function () {
		$("#mask").css({ "display": "block" })
		getGroupDown()
	})

	//关闭弹框
	$("#close").on("touchend", function () {
		$("#mask").css("display", "none")
		$("#content").html("")
	})


	//清楚缓存 api/clearCache
	function clear() {
		$.ajax({
			url: baseUrl + "/api/clearCache",
			type: "get",
			success: function (res) {
				console.log(res);
			},
			error: function (error) {
				return error
			}
		})
	}
	//获取token
	function getToken() {
		$.ajax({
			url: baseUrl + "/api/getAccessToken",
			type: "get",
			success: function (res) {
				console.log(res);
			},
			error: function (error) {
				return error
			}
		})
	}

	//获取用户信息
	function getUserInfo() {
		$.ajax({
			url: baseUrl + "/api/getUserInfo",
			type: "get",
			dataType: "json",
			success: function (res) {
				console.log(res);
				let data = JSON.stringify(res)
				$("#content").html(data)
			},
			error: function (e) {
				console.log(e);
			}
		})
	}

	//获取用户敏感信息
	function getUserSecret() {
		$.ajax({
			url: baseUrl + "/api/getUserSensitiveInfo",
			type: "get",
			dataType: "json",
			success: function (res) {
				console.log(res);
				let data = JSON.stringify(res)
				$("#content").html(data)
				// if (res.code == 200) {
				// 	getAccessToken()
				// }
			},
			error: function (e) {
				console.log(e);
			}
		})
	}

	// // 获取授权接口
	// function getAlwaysPass() {
	// 	$.ajax({
	// 		url: baseUrl + "/api/getAlwaysPass",
	// 		type: "get",
	// 		dataType: "json",
	// 		success: function (res) {
	// 			console.log(res);
	// 			if (res.code == 200) {
	// 				getAccessToken()
	// 			}
	// 		},
	// 		error: function (e) {
	// 			console.log(e);
	// 		}
	// 	})
	// }

	// // 获取access_token
	// function getAccessToken() {
	// 	$.ajax({
	// 		url: baseUrl + "/api/getTokenTow",
	// 		type: "post",
	// 		dataType: "json",
	// 		success: function (res) {
	// 			console.log(res);
	// 		},
	// 		error: function (e) {
	// 			console.log(e);
	// 		}
	// 	})
	// }

	//组织树
	function getGroupTree() {
		$.ajax({
			url: baseUrl + "/api/getGroupTree",
			type: "get",
			dataType: "json",
			success: function (res) {
				console.log(res);
				let data = JSON.stringify(res)
				$("#content").html(data)
			},
			error: function (e) {
				console.log(e);
			}
		})
	}

	//获取组织列表
	function getGroupList() {
		$.ajax({
			url: baseUrl + "/api/getGroupList",
			type: "get",
			dataType: "json",
			success: function (res) {
				console.log(res);
				let data = JSON.stringify(res)
				$("#content").html(data)
			},
			error: function (e) {
				console.log(e);
			}
		})
	}

	//获取党组织下属机构
	function getGroupDown() {
		$.ajax({
			url: baseUrl + "/api/getGroupDown",
			type: "get",
			dataType: "json",
			success: function (res) {
				console.log(res);
				let data = JSON.stringify(res)
				$("#content").html(data)
			},
			error: function (e) {
				console.log(e);
			}
		})
	}

	//获取委员会成员
	function getCommitteeList() {
		$.ajax({
			url: baseUrl + "/api/getCommitteeList",
			type: "get",
			dataType: "json",
			success: function (res) {
				console.log(res);
				let data = JSON.stringify(res)
				$("#content").html(data)
			},
			error: function (e) {
				console.log(e);
			}
		})
	}

	//获取支部党员
	function getListPeople() {
		$.ajax({
			url: baseUrl + "/api/getListPeople",
			type: "get",
			dataType: "json",
			success: function (res) {
				console.log(res);
				let data = JSON.stringify(res)
				$("#content").html(data)
			},
			error: function (e) {
				console.log(e);
			}
		})
	}

	//获取单个用户个人信息
	function getGroupSingeInfo() {
		$.ajax({
			url: baseUrl + "/api/getGroupSingeInfo",
			type: "get",
			dataType: "json",
			success: function (res) {
				console.log(res);
				let data = JSON.stringify(res)
				$("#content").html(data)
			},
			error: function (e) {
				console.log(e);
			}
		})
	}

	//获取用户迁入迁出部记录
	function getUserInOrOut() {
		$.ajax({
			url: baseUrl + "/api/getUserInOrOut",
			type: "get",
			dataType: "json",
			success: function (res) {
				console.log(res);
				let data = JSON.stringify(res)
				$("#content").html(data)
			},
			error: function (e) {
				console.log(e);
			}
		})
	}




</script>