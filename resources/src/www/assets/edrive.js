$(function () {
	var rest = 'rest' in ed ? ed.rest : 'ed.php';
	var domain = "https://vault.elephantdrive.com";
	if ('domain' in ed) {
		domain = ed.domain;
	}
	$.ajaxSetup({cache: false});

	createREST = function (params) {
		if (typeof params === "object") {
			params = $.param(params);
		}
		var query = window.location.search.substring(1);
		if (query.length > 0) {
			query = "&" + query;
		}
		return rest + "?" + params + query;
	};

	loadingMode = function (message) {
		$('#signupScreen').hide();
		$('#statusScreen').hide();
		$('#loginScreen').hide();
		$('#loadingMessage').text(message);
		$('#loadingScreen').show();
	};

	signupMode = function () {
		$('#signupScreen').show();
		$('#loadingScreen').hide();
		$('#statusScreen').hide();
		$('#loginScreen').hide();
		$('#signupAlert').hide();
		$('#inputEmailS').val('');
		$('#inputPasswordS').val('');
		$('#inputPasswordConfirm').val('');
	};

	statusMode = function () {
		$('#signupScreen').hide();
		$('#loadingScreen').hide();
		$('#statusScreen').show();
		$('#loginScreen').hide();
	};

	nasAuthError = function (httpObj) {
		if (httpObj.status === 401) {
			$('#signupScreen').hide();
			$('#loadingScreen').hide();
			$('#statusScreen').hide();
			$('#loginScreen').hide();
			$('.nas-apps-config-form-app-version').hide();
			$.get(createREST('q=authinfo'), function (response) {
				if (response) {
					$('#loginToYourNAS').prop("href", response.url);
				}
			});
			$('#authNASScreen').show();
		}
	};

	loginMode = function () {
		$('#loadingScreen').hide();
		$('#statusScreen').hide();
		$('#signupScreen').hide();
		$('#loginScreen').show();
	};

	startMode = function () {
		$('#loginAlert').hide();
		$('#inputEmail').val('');
		$('#inputPassword').val('');
		$.get(createREST('q=info'), function (response) {
			if (response) {
				loginMode();
				if (response.user.length !== 0) {
					statusMode();
				}
				$('#version').html(response.version !== null ? response.version : '&nbsp;');
				$('#username').text(response.user);
			}
		}).fail(nasAuthError);
	};

	onLoginFailure = function (response) {
		//Show notification: Username/password is wrong
		if ('message' in response) {
			$('#loginAlert').html(response.message);
		} else {
			$('#loginAlert').text($('#wrongLoginOrPasswordMessage').text());
		}
		$('#loginAlert').show();
		loginMode();
	}

	doLoginCall = function () {
		$('#inputEmail').val($('#inputEmail').val().trim());
		var checkURL = domain + '/partners/vaultservices/login.aspx';
		var checkParams = {email: $('#inputEmail').val(), p: $('#inputPassword').val()};

		$.get(checkURL + "?" + $.param(checkParams), function (response) {
			if (response && response.success == 'true') {
				var ajaxParams = {q: "dologin", user: $('#inputEmail').val(), pass: $('#inputPassword').val()};
				$.get(createREST(ajaxParams), function (response) {
					if (response) {
						if (response.success === true || response.success === 'true') {
							//Show logged in screen
							$('#username').text($('#inputEmail').val());
							statusMode();
							$('#statusAlert').show();
						} else {
							onLoginFailure(response);
						}
					}
				}).fail(nasAuthError);
			} else {
				onLoginFailure(response);
			}
		}, 'jsonp');
	};

	$('#loginBtn').click(function (e) {
		e.preventDefault();
		if ($('#inputEmail').val().trim().length === 0 || $('#inputPassword').val().trim().length === 0) {
			return;
		}
		$('#loginAlert').hide();
		loadingMode($('#loginLoadingMessage').text());
		doLoginCall();
	});


	$("#inputEmail, #inputPassword").keypress(function (e) {
		if (e.which === 13) {
			$("#loginBtn").trigger("click");
		}
	});

	$('#logoutBtn').click(function (e) {
		e.preventDefault();

		$.get(createREST('q=dologout'), function (response) {
			if (response.success === true || response.success === 'true') {
				startMode();
			}
		}).fail(nasAuthError);
	});

	$('#manageBackups').click(function (e) {
		e.preventDefault();
		$.ajax({
			url: createREST('q=autologintoken'),
			async: false
		}).done(function (response) {
			if (response) {
				response["tab"] = "naswizard";
				window.open(domain + "/account/autologin.aspx?" + $.param(response), "_blank");
			}
		}).fail(nasAuthError);
	});

	$('#logFileBtn').click(function (e) {
		e.preventDefault();
		window.open(createREST('q=log'), "_blank");
	});

	$('#forgotPassBtn').click(function (e) {
		e.preventDefault();
		window.open("https://vault.elephantdrive.com/account/forgot_password.aspx?UName=" + encodeURIComponent($('#inputEmail').val()), "_blank");
	});

	$('#goToSignupBtn').click(function (e) {
		e.preventDefault();
		signupMode();
	});

	$('#signupBackToLoginBtn').click(function (e) {
		e.preventDefault();
		startMode();
	});

	onSignupFailure = function (response) {
		//Signup FAIL
		$('#loadingScreen').hide();
		$('#signupScreen').show();
		if ('message' in response) {
			$('#signupAlert').html(response.message);
		} else {
			$('#signupAlert').text($('#wrongLoginOrPasswordMessage').text());
		}
		$('#signupAlert').show();
	}

	$('#signupBtn').click(function (e) {
		e.preventDefault();

		if ($('#inputPasswordS').val() !== $('#inputPasswordConfirm').val()) {
			$('#signupAlert').text($('#passwordDoNotMatchMessage').text());
			$('#signupAlert').show();
			return;
		}

		$('#signupScreen').hide();
		$('#loadingMessage').text($('#signupLoadingMessage').text());
		$('#loadingScreen').show();

		var ajaxParams = {q: "getsignupurl", user: $('#inputEmailS').val(), pass: $('#inputPasswordS').val(), c: ed.partnerId};

		$.get(createREST(ajaxParams), function (response) {
			if (response) {
				if (response.success === true || response.success === 'true') {
					$.get(response.url, function (response) {
						if (response) {
							if (response.success === true || response.success === 'true') {
								$('#inputEmail').val($('#inputEmailS').val());
								$('#inputPassword').val($('#inputPasswordS').val());
								doLoginCall();
							} else {
								onSignupFailure(response);
							}
						}
					}, 'jsonp');
				} else {
					onSignupFailure(response);
				}
			}
		}).fail(nasAuthError);

	}); //signupBtn.click

	$("#inputEmailS, #inputPasswordS, #inputPasswordConfirm").keypress(function (e) {
		if (e.which === 13) {
			$("#signupBtn").trigger("click");
		}
	});

	main = function () {
		$('title').text(ed.applicationName);
		$('.nas-os-name').text(ed.nasOSName);
		var basicEDWebsite = "http://www.elephantdrive.com";
		if ('nasVisitEDWebsite' in ed) {
			$('.nas-visit-ed-website').prop("href", ed.nasVisitEDWebsite);
		} else {
			$('.nas-visit-ed-website').prop("href", basicEDWebsite);
		}
		if ('nasVisitEDWebsiteLearnMore' in ed) {
			$('.nas-visit-ed-website-learn-more').prop("href", ed.nasVisitEDWebsiteLearnMore);
		} else {
			$('.nas-visit-ed-website-learn-more').prop("href", basicEDWebsite);
		}
		startMode();
	};

	main();

});