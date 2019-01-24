/*--------------------------------------------------------------------------------------------------------
# VP One Page Checkout - Joomla! System Plugin for VirtueMart 3
----------------------------------------------------------------------------------------------------------
# Copyright:     Copyright (C) 2012 - 2015 VirtuePlanet Services LLP. All Rights Reserved.
# License:       GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
# Author:        Abhishek Das
# Email:         info@virtueplanet.com
# Websites:      http://www.virtueplanet.com
----------------------------------------------------------------------------------------------------------
$Revision: 55 $
$LastChangedDate: 2015-09-15 02:10:01 +0530 (Tue, 15 Sep 2015) $
$Id: plugin.js 55 2015-09-14 20:40:01Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
VPOPC = window.VPOPC || {};
VPOPC.option = {};
VPOPC.loadOptions = function(e) {
    for (var t in e) {
        this.option[t] = e[t]
    }
    return this
};
VPOPC.JText = {
    strings: {},
    _: function(e, t) {
        return typeof this.strings[e.toUpperCase()] !== "undefined" ? this.strings[e.toUpperCase()] : t
    },
    sprintf: function(e, t) {
        var n = typeof this.strings[e.toUpperCase()] !== "undefined" ? this.strings[e.toUpperCase()] : "";
        return n.replace("%s", t)
    },
    load: function(e) {
        for (var t in e) {
            this.strings[t.toUpperCase()] = e[t]
        }
        return this
    }
};
if (typeof ProOPC === "undefined" || typeof ProOPC === undefined) {
    var jq = jQuery.noConflict();
    var ProOPC = {
        ajaxPool: {},
        spinnervars: function() {
            var e = {
                lines: 13,
                length: 3,
                width: 2,
                radius: 5,
                corners: 1,
                rotate: 0,
                direction: 1,
                color: "#FFF",
                speed: 1.5,
                trail: 60,
                shadow: false,
                hwaccel: false,
                className: "proopc-spinner",
                zIndex: 2e9,
                top: "auto",
                left: "auto"
            };
            proopc_spinner = (new VPSpinner(e)).spin();
            var t = {
                lines: 10,
                length: 10,
                width: 4,
                radius: 15,
                corners: 1,
                rotate: 0,
                direction: 1,
                color: VPOPC.option.SPINNER_COLOR,
                speed: 1.5,
                trail: 60,
                shadow: false,
                hwaccel: true,
                className: "proopc-page-loader",
                zIndex: 2e9,
                top: 20,
                left: 14
            };
            proopc_loader = (new VPSpinner(t)).spin();
            var n = {
                lines: 10,
                length: 5,
                width: 3,
                radius: 8,
                corners: 1,
                rotate: 0,
                direction: 1,
                color: VPOPC.option.SPINNER_COLOR,
                speed: 1.5,
                trail: 40,
                shadow: false,
                hwaccel: true,
                className: "proopc-area-loader",
                zIndex: 2e9,
                top: 20,
                left: 14
            };
            proopc_area_loader = (new VPSpinner(n)).spin()
        },
        opcmethod: function() {
            var e = jq('input:radio[name="proopc-method"]:checked').val();
            if (e == "guest") {
                jq(".proopc-reg-form").hide().css("opacity", 0);
                jq(".proopc-reg-advantages, .proopc-guest-form").show().animate({
                    opacity: 1
                }, 500);
                ProOPC.inputwidth();
                ProOPC.selectwidth()
            } else {
                jq(".proopc-reg-form").show().animate({
                    opacity: 1
                }, 500, function() {
                    if (jq("#ProOPC").find("#dynamic_recaptcha_1").length) {
                        ProOPC.style()
                    }
                });
                jq(".proopc-reg-advantages, .proopc-guest-form").hide().css("opacity", 0);
                ProOPC.inputwidth();
                ProOPC.selectwidth()
            }
        },
        guestcheckout: function() {
            jq.ajax({
                type: "POST",
                url: VPOPC.option.URI,
                data: jq("#GuestUser").serialize(),
                cache: false,
                beforeSend: function() {
                    if (ProOPC.validateForm("#GuestUser") == false) {
                        return false
                    }
                    jq("#proopc-guest-process").append(proopc_spinner.el)
                },
                success: function(e) {
                    jq("#proopc-guest-process .proopc-spinner").remove();
                    ProOPC.setmsg("info", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_EMAIL_SAVED"), false);
                    ProOPC.processCheckout({
                        error: 0
                    })
                }
            });
            return false
        },
        verifyRegForm: function() {
            jq('#UserLogin input[type="text"]:not(#proopc-secretkey), #UserLogin input[type="password"]').keyup(function(e) {
                if (jq(this).val() == "") {
                    jq(this).siblings(".status").addClass("invalid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_REQUIRED_FIELD"))
                } else {
                    jq(this).siblings(".status").removeClass("invalid").removeAttr("title")
                }
            });
            jq('#GuestUser input[type="text"]').keyup(function(e) {
                var t = jq(this);
                if (jq(this).attr("id") == "email_field") {
                    var n = jq(t).val();
                    if (ProOPC.validateEmail(n)) {
                        jq(t).removeClass("invalid").addClass("valid");
                        jq(t).siblings(".status").removeClass("invalid").addClass("valid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_VALIDATED"))
                    } else {
                        jq(t).removeClass("valid").addClass("invalid");
                        jq(t).siblings(".status").removeClass("valid").addClass("invalid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_EMAIL_INVALID"))
                    }
                }
            });
            jq('#UserRegistration input[type="text"], #UserRegistration input[type="password"]').keyup(function(e) {
                var t = jq(this);
                if (jq(this).attr("id") == "email_field") {
                    var n = jq(t).val();
                    var r = this;
                    if (ProOPC.validateEmail(n)) {
                        if (VPOPC.option.AJAXVALIDATION == 1) {
                            if (ProOPC.ajaxPool.hasOwnProperty("checkemail") && ProOPC.ajaxPool["checkemail"].readystate != 4) {
                                ProOPC.ajaxPool["checkemail"].abort()
                            }
                            ProOPC.ajaxPool["checkemail"] = jq.ajax({
                                beforeSend: function(e) {
                                    jq(t).siblings(".status").removeClass("hover-tootip").removeClass("invalid").removeClass("valid").addClass("validating")
                                },
                                dataType: "JSON",
                                url: VPOPC.option.URI,
                                data: "ctask=checkemail&email=" + n,
                                cache: false,
                                success: function(e) {
                                    if (e.valid !== 1) {
                                        jq(t).removeClass("valid").addClass("invalid");
                                        jq(t).siblings(".status").removeClass("valid").removeClass("validating").addClass("hover-tootip").addClass("invalid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_INVALID"));
                                        jq(r).addClass("hasFieldTip").attr("title", VPOPC.JText._("COM_USERS_PROFILE_EMAIL1_MESSAGE"));
                                        ProOPC.fieldTip();
                                        ProOPC.showFieldTip(r)
                                    } else {
                                        ProOPC.removeFieldTip(r, false);
                                        jq(r).attr("title", "").removeClass("hasFieldTip");
                                        jq(t).removeClass("invalid").addClass("valid");
                                        jq(t).siblings(".status").removeClass("validating").removeClass("invalid").addClass("hover-tootip").addClass("valid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_VALIDATED"))
                                    }
                                }
                            })
                        } else {
                            jq(t).removeClass("invalid").addClass("valid");
                            jq(t).siblings(".status").removeClass("invalid").addClass("valid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_VALIDATED"))
                        }
                    } else {
                        jq(t).removeClass("valid").addClass("invalid");
                        jq(t).siblings(".status").removeClass("valid").addClass("invalid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_EMAIL_INVALID"))
                    }
                } else {
                    if (jq(this).attr("id") == "username_field") {
                        var i = jq(t).val();
                        var s = this;
                        if (ProOPC.validateUsername(i)) {
                            if (VPOPC.option.AJAXVALIDATION == 1) {
                                if (ProOPC.ajaxPool.hasOwnProperty("checkusername") && ProOPC.ajaxPool["checkusername"].readystate != 4) {
                                    ProOPC.ajaxPool["checkusername"].abort()
                                }
                                ProOPC.ajaxPool["checkusername"] = jq.ajax({
                                    dataType: "JSON",
                                    url: VPOPC.option.URI,
                                    data: "ctask=checkuser&username=" + i,
                                    cache: false,
                                    beforeSend: function(e) {
                                        jq(t).siblings(".status").removeClass("hover-tootip").removeClass("invalid").removeClass("valid").addClass("validating")
                                    },
                                    success: function(e) {
                                        if (e.valid !== 1) {
                                            jq(t).removeClass("valid").addClass("invalid");
                                            jq(t).siblings(".status").removeClass("valid").removeClass("validating").addClass("hover-tootip").addClass("invalid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_INVALID"));
                                            jq(s).addClass("hasFieldTip").attr("title", VPOPC.JText._("COM_USERS_PROFILE_USERNAME_MESSAGE"));
                                            ProOPC.fieldTip();
                                            ProOPC.showFieldTip(s)
                                        } else {
                                            ProOPC.removeFieldTip(s, false);
                                            jq(s).attr("title", "").removeClass("hasFieldTip");
                                            jq("#proopc-system-message").html("");
                                            jq(t).removeClass("invalid").addClass("valid");
                                            jq(t).siblings(".status").removeClass("invalid").removeClass("validating").addClass("hover-tootip").addClass("valid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_VALIDATED"))
                                        }
                                    }
                                })
                            } else {
                                jq(t).removeClass("invalid").addClass("valid");
                                jq(t).siblings(".status").removeClass("invalid").addClass("valid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_VALIDATED"))
                            }
                        } else {
                            jq(t).removeClass("valid").addClass("invalid");
                            jq(t).siblings(".status").removeClass("valid").addClass("invalid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_USERNAME_INVALID"))
                        }
                    } else {
                        if (jq(this).attr("id") == "password_field") {
                            var o = jq(t).val();
                            var u = jq("#password2_field");
                            u.removeClass("valid");
                            u.siblings(".status").removeClass("valid");
                            ProOPC.removeFieldTip(t, false);
                            if (o == "") {
                                jq("#password-stregth, #meter-status").attr("class", "").addClass("invalid");
                                jq(t).removeClass("valid").addClass("invalid");
                                jq(t).siblings(".status").removeClass("valid").addClass("invalid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_INVALID"));
                                jq("#password-stregth").text("")
                            } else if (!ProOPC.validatePassword(t)) {
                                jq("#password-stregth, #meter-status").attr("class", "").addClass("invalid");
                                jq(t).removeClass("valid").addClass("invalid");
                                jq(t).siblings(".status").removeClass("valid").addClass("invalid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_INVALID"));
                                jq("#password-stregth").text("")
                            } else {
                                ProOPC.checkStrength(o)
                            }
                        } else {
                            if (jq(this).attr("id") == "password2_field") {
                                var a = jq("#password_field").val();
                                var f = jq(t).val();
                                if (f !== a || f == "") {
                                    jq(t).removeClass("valid").addClass("invalid");
                                    jq(t).siblings(".status").removeClass("valid").addClass("invalid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_INVALID"))
                                } else {
                                    jq(t).removeClass("invalid").addClass("valid");
                                    jq(t).siblings(".status").removeClass("invalid").addClass("valid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_VALIDATED"))
                                }
                            } else {
                                if (jq(this).attr("id") == "name_field") {
                                    var l = jq(t).val();
                                    if (l.length == 0) {
                                        jq(t).removeClass("valid").addClass("invalid");
                                        jq(t).siblings(".status").removeClass("valid").addClass("invalid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_INVALID"))
                                    } else {
                                        jq(t).removeClass("invalid").addClass("valid");
                                        jq(t).siblings(".status").removeClass("invalid").addClass("valid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_VALIDATED"))
                                    }
                                }
                            }
                        }
                    }
                }
            })
        },
        validateEmail: function(e) {
            if (VPOPC.option.DISABLELIVEVALIDATION) {
                return true
            }
            var t = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
            if (!t.test(e) || e.length < 5) {
                return false
            } else {
                return true
            }
        },
        validateUsername: function(e) {
            if (VPOPC.option.DISABLELIVEVALIDATION) {
                return true
            }
            var t = /^[a-zA-Z0-9]+$/;
            if (!t.test(e)) {
                return false
            } else {
                return true
            }
        },
        validatePassword: function(e) {
        		var t = jq(e);
            if (VPOPC.option.DISABLELIVEVALIDATION) {
                t.removeClass("invalid").addClass("valid");
                t.siblings(".status").removeClass("invalid").addClass("valid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_VALIDATED"));
                return true
            }
            var n = t.val();
            ProOPC.removeFieldTip(e, false);
            t.attr("title", "").removeClass("hasFieldTip");
            var r = false;
            if (n.indexOf(" ") >= 0) {
                t.addClass("hasFieldTip").attr("title", VPOPC.JText._("COM_USERS_MSG_SPACES_IN_PASSWORD"));
                r = true
            } else if (VPOPC.option.PASSWORD_INTEGERS || VPOPC.option.PASSWORD_SYMBOLS || VPOPC.option.PASSWORD_UPPERCASE) {
                var i = n.match(/\d/g);
                var s = n.match(/\W/g);
                var o = n.match(/[A-Z]/g);
                if (VPOPC.option.PASSWORD_INTEGERS > 0 && (!i || i.length < VPOPC.option.PASSWORD_INTEGERS)) {
                    if (VPOPC.option.PASSWORD_INTEGERS == 1) {
                        t.addClass("hasFieldTip").attr("title", VPOPC.JText.sprintf("COM_USERS_MSG_NOT_ENOUGH_INTEGERS_N_1", VPOPC.option.PASSWORD_INTEGERS))
                    } else {
                        t.addClass("hasFieldTip").attr("title", VPOPC.JText.sprintf("COM_USERS_MSG_NOT_ENOUGH_INTEGERS_N", VPOPC.option.PASSWORD_INTEGERS))
                    }
                    r = true
                } else if (VPOPC.option.PASSWORD_SYMBOLS > 0 && (!s || s.length < VPOPC.option.PASSWORD_SYMBOLS)) {
                    if (VPOPC.option.PASSWORD_SYMBOLS == 1) {
                        t.addClass("hasFieldTip").attr("title", VPOPC.JText.sprintf("COM_USERS_MSG_NOT_ENOUGH_SYMBOLS_N_1", VPOPC.option.PASSWORD_SYMBOLS))
                    } else {
                        t.addClass("hasFieldTip").attr("title", VPOPC.JText.sprintf("COM_USERS_MSG_NOT_ENOUGH_SYMBOLS_N", VPOPC.option.PASSWORD_SYMBOLS))
                    }
                    r = true
                } else if (VPOPC.option.PASSWORD_UPPERCASE > 0 && (!o || o.length < VPOPC.option.PASSWORD_UPPERCASE)) {
                    if (VPOPC.option.PASSWORD_UPPERCASE == 1) {
                        t.addClass("hasFieldTip").attr("title", VPOPC.JText.sprintf("COM_USERS_MSG_NOT_ENOUGH_UPPERCASE_LETTERS_N_1", VPOPC.option.PASSWORD_UPPERCASE))
                    } else {
                        t.addClass("hasFieldTip").attr("title", VPOPC.JText.sprintf("COM_USERS_MSG_NOT_ENOUGH_UPPERCASE_LETTERS_N", VPOPC.option.PASSWORD_UPPERCASE))
                    }
                    r = true
                }
            } else if (VPOPC.option.PASSWORD_LENGTH > 0 && n.length < VPOPC.option.PASSWORD_LENGTH) {
                t.addClass("hasFieldTip").attr("title", VPOPC.JText.sprintf("COM_USERS_MSG_PASSWORD_TOO_SHORT_N", VPOPC.option.PASSWORD_LENGTH));
                r = true
            }
            if (r) {
                ProOPC.fieldTip();
                ProOPC.showFieldTip(e);
                return false
            }
            return true
        },
        checkStrength: function(e) {
            if (VPOPC.option.DISABLELIVEVALIDATION) {
                jq("#password_field").removeClass("invalid").addClass("valid");
                jq("#password_field").siblings(".status").removeClass("invalid").addClass("valid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_VALIDATED"));
                return true
            }
            var t = 0;
            if (e.length < 4) {
                jq("#password-stregth, #meter-status").attr("class", "").addClass("short");
                jq("#password_field").removeClass("valid").addClass("invalid");
                jq("#password_field").siblings(".status").removeClass("valid").addClass("invalid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_INVALID"));
                jq("#password-stregth").text(VPOPC.JText._("PLG_VPONEPAGECHECKOUT_TOO_SHORT"));
                return false
            }
            if (e.length > 4) {
                t += 1
            }
            if (e.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) {
                t += 1
            }
            if (e.match(/([a-zA-Z])/) && e.match(/([0-9])/)) {
                t += 1
            }
            if (e.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) {
                t += 1
            }
            if (e.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,",%,&,@,#,$,^,*,?,_,~])/)) {
                t += 1
            }
            if (t < 2) {
                jq("#password-stregth, #meter-status").attr("class", "").addClass("weak");
                jq("#password_field").removeClass("invalid").addClass("valid");
                jq("#password_field").siblings(".status").removeClass("invalid").addClass("valid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_VALIDATED"));
                jq("#password-stregth").text(VPOPC.JText._("PLG_VPONEPAGECHECKOUT_WEAK"))
            } else {
                if (t == 2) {
                    jq("#password-stregth, #meter-status").attr("class", "").addClass("good");
                    jq("#password_field").removeClass("invalid").addClass("valid");
                    jq("#password_field").siblings(".status").removeClass("invalid").addClass("valid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_VALIDATED"));
                    jq("#password-stregth").text(VPOPC.JText._("PLG_VPONEPAGECHECKOUT_GOOD"))
                } else {
                    jq("#password-stregth, #meter-status").attr("class", "").addClass("strong");
                    jq("#password_field").removeClass("invalid").addClass("valid");
                    jq("#password_field").siblings(".status").removeClass("invalid").addClass("valid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_VALIDATED"));
                    jq("#password-stregth").text(VPOPC.JText._("PLG_VPONEPAGECHECKOUT_STRONG"))
                }
            }
        },
        registerCheckout: function() {
            var e = "&ctask=register";
            jq.ajax({
                dataType: "json",
                type: "POST",
                beforeSend: function() {
                    if (ProOPC.validateForm("#UserRegistration") == false) {
                        return false
                    }
                    jq("#proopc-register-process").append(proopc_spinner.el)
                },
                url: VPOPC.option.URI,
                data: jq("#UserRegistration").serialize() + e,
                cache: false,
                success: function(e) {
                    if (e.msg) {
                        if (e.error == 1) {
                            jq("#proopc-register-process .proopc-spinner").remove();
                            ProOPC.setmsg("1", e.msg);
                            if (e.reload) {
                                window.location.reload()
                            }
                            if (typeof Recaptcha !== "undefined") {
                                Recaptcha.reload()
                            }
                            jq(document).trigger("vpopc.event", ["registration.failed"])
                        } else {
                            ProOPC.setmsg("3", e.msg);
                            jq(document).trigger("vpopc.event", ["registration.success"]);
                            if (e.stop == 1) {
                                jq("#proopc-register-process .proopc-spinner").remove()
                            } else {
                                setTimeout(function() {
                                    jq("#proopc-register-process .proopc-spinner").remove();
                                    if (VPOPC.option.RELOAD) {
                                        window.location.reload()
                                    } else {
                                        ProOPC.processCheckout(e)
                                    }
                                }, 3e3)
                            }
                        }
                    } else {
                        if (e == "1" || e == 1) {
                            jq("#proopc-register-process .proopc-spinner").remove();
                            ProOPC.setmsg("3", VPOPC.JText._("COM_VIRTUEMART_REG_COMPLETE"));
                            jq(document).trigger("vpopc.event", ["registration.success"]);
                            if (VPOPC.option.RELOAD) {
                                window.location.reload()
                            } else {
                                ProOPC.processCheckout({
                                    error: 0
                                })
                            }
                        } else {
                            if (jq.type(e) === "string" && e.indexOf("</head>") == -1) {
                                jq("#proopc-register-process .proopc-spinner").remove();
                                ProOPC.setmsg("3", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_REGISTRATION_NEED_LOGIN"));
                                jq(document).trigger("vpopc.event", ["registration.failed"])
                            } else {
                                jq("#proopc-register-process .proopc-spinner").remove();
                                ProOPC.setmsg("3", VPOPC.JText._("COM_VIRTUEMART_REG_COMPLETE"));
                                jq(document).trigger("vpopc.event", ["registration.success"]);
                                if (VPOPC.option.RELOAD) {
                                    window.location.reload()
                                } else {
                                    ProOPC.processCheckout({
                                        error: 0
                                    })
                                }
                            }
                        }
                    }
                },
                error: function(e, t, n) {
                    console.log(e);
                    console.log(t);
                    console.log(n);
                    jq("#proopc-register-process .proopc-spinner").remove();
                    ProOPC.setmsg("1", "Error submiting registration form (registerCheckout). Reload the page and try again.")
                }
            });
            return false
        },
        processCheckout: function(e) {
            ProOPC.addpageloader();
            jq("#proopc-page-spinner").after('<div id="proopc-order-process"></div>');
            jq("#proopc-order-process").text(VPOPC.JText._("PLG_VPONEPAGECHECKOUT_PLEASE_WAIT"));
            if (e.error == 0) {
                var e = jq("#ProOPC").html();
                var t = ProOPC.getUrlVars();
                jq.ajax({
                    url: VPOPC.option.URI,
                    data: "task=procheckout&" + t,
                    dataType: "html",
                    cache: false,
                    success: function(e) {
                        var t = jq(e).find("#ProOPC").html();
                        if (t == null) {
                            var t = e
                        }
                        jq("#ProOPC").html(t);
                        jq(document).trigger("vpopc.event", ["checkout.finalstage"]);
                        if (!VPOPC.option.RELOAD) {
                            jq("html, body").animate({
                                scrollTop: jq("#proopc-system-message").offset().top - 100
                            }, 500)
                        }
                    },
                    complete: function() {
                        var e = jq("input#BTStateID").val();
                        var t = jq("input#STStateID").val();
                        jq("#virtuemart_country_id").vm2front("list", {
                            dest: "#virtuemart_state_id",
                            ids: e,
                            prefiks: ""
                        });
                        jq("#shipto_virtuemart_country_id").vm2front("list", {
                            dest: "#shipto_virtuemart_state_id",
                            ids: t,
                            prefiks: "shipto_"
                        });
                        jq("#virtuemart_country_id").change(function() {
                            if (jq("#virtuemart_state_id optgroup").length > 0) {
                                jq("#virtuemart_state_id optgroup").remove();
                                jq("#virtuemart_country_id").vm2front("list", {
                                    dest: "#virtuemart_state_id",
                                    ids: "",
                                    prefiks: ""
                                })
                            }
                        });
                        jq("#shipto_virtuemart_country_id").change(function() {
                            if (jq("#shipto_virtuemart_state_id optgroup").length > 0) {
                                jq("#shipto_virtuemart_state_id optgroup").remove();
                                jq("#shipto_virtuemart_country_id").vm2front("list", {
                                    dest: "#shipto_virtuemart_state_id",
                                    ids: "",
                                    prefiks: "shipto_"
                                })
                            }
                        });
                        ProOPC.style();
                        ProOPC.tooltip();
                        ProOPC.inputwidth();
                        ProOPC.selectwidth();
                        jq("#proopc-order-process").remove();
                        ProOPC.removepageloader();
                        ProOPC.defaultSP();
                        if (typeof klarna === "undefined") {
                            klarnaExits = false
                        } else {
                            klarnaExits = true
                        }
                        if (klarnaExits) {
                            ProOPC.loadPaymentScripts()
                        }
                        ProOPC.loadShipmentScripts()
                    }
                })
            } else {
                var n = "";
                jq.each(e.msg, function(e, t) {
                    n = n + '<div class="error-msg">' + t + "</div>"
                });
                jq("#proopc-system-message").html(n);
                jq("html,body").animate({
                    scrollTop: jq("#proopc-system-message").offset().top - 100
                }, 500);
                jq("#proopc-system-message").children("span").animate({
                    opacity: 0
                }, 2e3)
            }
        },
        loginAjax: function() {
            if (ProOPC.validateForm("#UserLogin") == false) {
                return false
            }
            var e = jq('form#UserLogin input[name="ctask"]:hidden');
            if (e.val() != "login") {
                e.val("login")
            }
            jq.ajax({
                type: "POST",
                beforeSend: function() {
                    jq("#proopc-login-process").append(proopc_spinner.el)
                },
                url: VPOPC.option.URI,
                data: jq("form#UserLogin").serialize(),
                cache: false,
                success: function(e, t, n) {
                    if (e == "1" || e == 1) {
                        jq("#proopc-login-process .proopc-spinner").remove();
                        ProOPC.setmsg("success", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_LOGIN_COMPLETED"));
                        jq(document).trigger("vpopc.event", ["login.success"]);
                        if (VPOPC.option.RELOAD) {
                            window.location.reload()
                        } else {
                            ProOPC.processCheckout({
                                error: 0
                            })
                        }
                    } else {
                        if (e.indexOf("</head>") == -1) {
                            jq("#proopc-login-process .proopc-spinner").remove();
                            ProOPC.setmsg("error", VPOPC.JText._("JLIB_LOGIN_AUTHENTICATE"));
                            jq(document).trigger("vpopc.event", ["login.failed"])
                        } else {
                            ProOPC.setmsg("success", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_LOGIN_COMPLETED"));
                            jq(document).trigger("vpopc.event", ["login.success"]);
                            if (VPOPC.option.RELOAD) {
                                window.location.reload()
                            } else {
                                ProOPC.processCheckout({
                                    error: 0
                                })
                            }
                        }
                    }
                },
                error: function(e, t, n) {
                    ProOPC.setmsg("1", "Login failed. Please refersh the page and try again.");
                    console.log(e);
                    console.log(t);
                    console.log(n)
                }
            });
            return false
        },
        updateBTaddress: function(e) {
            jq("#proopc-order-submit").attr("disabled", "disabled");
            if (jq(e).attr("id") == "virtuemart_country_id" || jq(e).attr("id") == "virtuemart_state_id") {
                var t = jq(e).val();
                jq(e).find('[selected="selected"]').removeAttr("selected");
                jq(e).find('option[value="' + t + '"]').attr("selected", "selected")
            }
            var n = jq("#EditBTAddres").serialize();
            var r = jq("#formToken input").attr("name");
            var i = jq("#formToken input").val();
            var s = "&ctask=savebtaddress&" + r + "=" + i;
            if (ProOPC.ajaxPool.hasOwnProperty("updateBTaddress") && ProOPC.ajaxPool["updateBTaddress"].readystate != 4) {
                ProOPC.ajaxPool["updateBTaddress"].abort()
            }
            ProOPC.ajaxPool["updateBTaddress"] = jq.ajax({
                type: "POST",
                url: VPOPC.option.URI,
                data: n + s,
                cache: false,
                success: function(e) {
                    jq(document).trigger("vpopc.event", ["checkout.bt.updated"]);
                    ProOPC.getshipmentpaymentcartlist()
                }
            })
        },
        updateSTaddress: function(e) {
            jq("#proopc-order-submit").attr("disabled", "disabled");
            if (jq(e).attr("id") == "shipto_virtuemart_country_id" || jq(e).attr("id") == "shipto_virtuemart_state_id") {
                var t = jq(e).val();
                jq(e).find('[selected="selected"]').removeAttr("selected");
                jq(e).find('option[value="' + t + '"]').attr("selected", "selected")
            }
            var n = jq("#EditSTAddres").serialize();
            var r = jq("#formToken input").attr("name");
            var i = jq("#formToken input").val();
            var s = "&ctask=savestaddress&" + r + "=" + i;
            if (ProOPC.ajaxPool.hasOwnProperty("updateSTaddress") && ProOPC.ajaxPool["updateSTaddress"].readystate != 4) {
                ProOPC.ajaxPool["updateSTaddress"].abort()
            }
            ProOPC.ajaxPool["updateSTaddress"] = jq.ajax({
                type: "POST",
                url: VPOPC.option.URI,
                data: n + s,
                cache: false,
                success: function(e) {
                    jq(document).trigger("vpopc.event", ["checkout.st.updated"]);
                    if (e.userinfo_id) {
                        jq('input#shipto_virtuemart_userinfo_id').val(e.userinfo_id);
                    }
                    ProOPC.getshipmentpaymentcartlist();
                }
            })
        },
        selectSTAddress: function(e) {
            var t = jq(e).val();
            if (ProOPC.ajaxPool.hasOwnProperty("selectSTAddress") && ProOPC.ajaxPool["selectSTAddress"].readystate != 4) {
                ProOPC.ajaxPool["selectSTAddress"].abort()
            }
            ProOPC.ajaxPool["selectSTAddress"] = jq.ajax({
                beforeSend: function(e) {
                    ProOPC.addloader("#proopc-st-address")
                },
                dataType: "json",
                type: "POST",
                url: VPOPC.option.URI,
                data: "ctask=selectstaddress&shipto_virtuemart_userinfo_id=" + t,
                cache: false,
                success: function(e) {
                    jq("#proopc-st-address").html(e.editst);
                    jq(document).trigger("vpopc.event", ["checkout.stselected"]);
                    jq("#shipto_virtuemart_country_id").vm2front("list", {
                        dest: "#shipto_virtuemart_state_id",
                        ids: '"' + e.stateid + '"',
                        prefiks: "shipto_"
                    })
                },
                complete: function(e) {
                    ProOPC.style();
                    ProOPC.inputwidth();
                    ProOPC.selectwidth();
                    ProOPC.removeloader("#proopc-st-address");
                    ProOPC.getshipmentpaymentcartlist()
                },
                error: function(e, t, n) {
                    console.log("Error: fetching shipping address");
                    console.log(e);
                    console.log(t);
                    console.log(n)
                }
            })
        },
        setst: function(e) {
            jq("#proopc-order-submit").attr("disabled", "disabled");
            if (jq(e).length > 0) {
                if (ProOPC.ajaxPool.hasOwnProperty("btasst") && ProOPC.ajaxPool["btasst"].readystate != 4) {
                    ProOPC.ajaxPool["btasst"].abort()
                }
                if (e.checked) {
                    ProOPC.ajaxPool["btasst"] = jq.ajax({
                        beforeSend: function(e) {
                            jq(".proopc-st-address .edit-address").slideUp()
                        },
                        type: "post",
                        url: VPOPC.option.URI,
                        data: "ctask=btasst",
                        cache: false,
                        success: function() {
                            jq(document).trigger("vpopc.event", ["checkout.btasst"]);
                            ProOPC.getshipmentpaymentcartlist()
                        }
                    })
                } else {
                    ProOPC.ajaxPool["btasst"] = jq.ajax({
                        beforeSend: function(e) {
                            jq(".proopc-st-address .edit-address").slideDown();
                            ProOPC.inputwidth();
                            ProOPC.selectwidth()
                        },
                        type: "post",
                        url: VPOPC.option.URI,
                        data: "ctask=btnotasst",
                        cache: false,
                        success: function(e) {
                            jq(document).trigger("vpopc.event", ["checkout.btnotasst"]);
                            var $selection = jq('select#proopc-select-st');
                            if ($selection.length && $selection.val() > 0) {
                                ProOPC.selectSTAddress($selection[0]);
                            } else {
                                ProOPC.updateSTaddress();
                            }
                        }
                    })
                }
            }
        },
        getshipmentpaymentcartlist: function() {
        		jq("#proopc-system-message").html("");
            ProOPC.addloader("#proopc-pricelist, #proopc-payments, #proopc-shipments");
            if (ProOPC.ajaxPool.hasOwnProperty("getshipmentpaymentcartlist") && ProOPC.ajaxPool["getshipmentpaymentcartlist"].readystate != 4) {
                ProOPC.ajaxPool["getshipmentpaymentcartlist"].abort()
            }
            ProOPC.ajaxPool["getshipmentpaymentcartlist"] = jq.ajax({
                dataType: "json",
                url: VPOPC.option.URI,
                data: "ctask=getshipmentpaymentcartlist",
                cache: false,
                success: function(e) {
                		if(e.msg) {
											ProOPC.setmsg(e.msg_type, e.msg);
										}
                    jq("#proopc-shipments").html(e.shipments);
                    jq("#proopc-payments").html(e.payments);
                    jq("#proopc-pricelist").html(e.cartlist);
                    if (e.payment_scripts.length > 0 && e.payment_scripts[0] !== "") {
                        payment_scripts = e.payment_scripts
                    }
                    if (e.payment_script[0] !== "") {
                        jq.each(e.payment_script, function(e, t) {
                            jq.getScript(t, function(e, t, n) {
                                if (typeof payment_scripts !== "undefined") {
                                    jq.each(payment_scripts, function(e, t) {
                                        t
                                    })
                                }
                            })
                        })
                    }
                    if (e.shipment_scripts.length > 0 && e.shipment_scripts[0] !== "") {
                        jq.each(e.shipment_scripts, function(e, t) {
                            jq("head").append('<script type="text/javascript">' + t + "</script>")
                        })
                    }
                },
                complete: function() {
                    ProOPC.style();
                    ProOPC.tooltip();
                    ProOPC.removeloader("#proopc-pricelist, #proopc-payments, #proopc-shipments");
                    ProOPC.defaultSP()
                },
                error: function(e, t, n) {
                    ProOPC.removeloader("#proopc-pricelist, #proopc-payments, #proopc-shipments");
                    console.log("Error: Error gettings Shipments, Payments and Cartlist (getshipmentpaymentcartlist).");
                    console.log(e);
                    console.log(t);
                    console.log(n)
                }
            })
        },
        defaultSP: function() {
            var e = "";
            if (VPOPC.option.AUTOSHIPMENT != -1) {
                var t = jq('#proopc-shipments input:radio[name="virtuemart_shipmentmethod_id"]:checked').val();
                if (jq('#proopc-shipments input:radio[name="virtuemart_shipmentmethod_id"]').length) {
                    var n = false;
                    if (jq("#proopc-savedShipment").val()) {
                        jq('#proopc-shipments input:radio[name="virtuemart_shipmentmethod_id"]').each(function() {
                            if (!t && jq(this).val() == jq("#proopc-savedShipment").val()) {
                                jq(this).attr("checked", true);
                                n = true;
                                return false
                            }
                        })
                    }
                    if (!n && !t) {
                        if (VPOPC.option.AUTOSHIPMENT > 0) {
                            jq('#proopc-shipments input:radio[name="virtuemart_shipmentmethod_id"][value="' + VPOPC.option.AUTOSHIPMENT + '"]').attr("checked", true);
                        } else {
                            jq('#proopc-shipments input:radio[name="virtuemart_shipmentmethod_id"]:first').attr("checked", true);
                        }
                        var r = jq('#proopc-shipments input:radio[name="virtuemart_shipmentmethod_id"]:checked').val();
                        e = e + "&virtuemart_shipmentmethod_id=" + r;
                        var i = jq('#proopc-shipments input:radio[name="virtuemart_shipmentmethod_id"]:checked').data("usps");
                        if (jq("#usps_name-" + r).length) {
                            var s = "usps_name-" + r + "=" + i.service;
                            e = e + "&" + s
                        }
                        if (jq("#usps_rate-" + r).length) {
                            var o = "usps_rate-" + r + "=" + i.rate;
                            e = e + "&" + o
                        }
                    }
                }
            }
            var u = jq('#proopc-payments input:radio[name="virtuemart_paymentmethod_id"]:first').attr("data-paypalproduct");
            if (VPOPC.option.AUTOPAYMENT != -1 && u != "exp") {
                var t = jq('#proopc-payments input:radio[name="virtuemart_paymentmethod_id"]:checked').val();
                if (jq('#proopc-payments input:radio[name="virtuemart_paymentmethod_id"]').length) {
                    var a = false;
                    if (jq("#proopc-savedPayment").val()) {
                        jq('#proopc-payments input:radio[name="virtuemart_paymentmethod_id"]').each(function() {
                            if (!t && t == jq("#proopc-savedPayment").val()) {
                                jq(this).attr("checked", true);
                                a = true;
                                return false
                            }
                        })
                    }
                    if (!a && !t) {
                        if (VPOPC.option.AUTOPAYMENT > 0) {
                            jq('#proopc-payments input:radio[name="virtuemart_paymentmethod_id"][value="' + VPOPC.option.AUTOPAYMENT + '"]').attr("checked", true);
                        } else {
                            jq('#proopc-payments input:radio[name="virtuemart_paymentmethod_id"]:first').attr("checked", true);
                        }
                        var f = jq('#proopc-payments input:radio[name="virtuemart_paymentmethod_id"]:checked').data("pmtype");
                        jq(".vmpayment_cardinfo").removeClass("show").addClass("hide");
                        jq(".vmpayment_cardinfo." + f).removeClass("hide").addClass("show");
                        var l = jq('#proopc-payments input:radio[name="virtuemart_paymentmethod_id"]:checked').val();
                        var c = 0;
                        if (jq(".vmpayment_cardinfo." + f).length > 0 || jq('#proopc-payments input:radio[name="virtuemart_paymentmethod_id"]:checked').data("pmtype") == "sisowideal") {
                            c = 1
                        } else {
                            if (jq('#proopc-payments input:radio[name="virtuemart_paymentmethod_id"]:checked').hasClass("klarnaPayment")) {
                                var h = jq('#proopc-payments input:radio[name="virtuemart_paymentmethod_id"]:checked').next('input[name="klarna_paymentmethod"]').val();
                                c = 1 + "&klarna_paymentmethod=" + h
                            }
                        }
                        var p = "";
                        if (jq('#proopc-payments input:radio[name="virtuemart_paymentmethod_id"]:checked').data("pmtype") == "piraeus") {
                            jq(".vmpayment_cardinfo.piraeus").find("select, input").each(function() {
                                p = p + "&" + jq(this).attr("name") + "=" + jq(this).val()
                            })
                        }
                        e = e + "&virtuemart_paymentmethod_id=" + l + "&payment_data=" + c + p
                    }
                }
            }
            if (e !== "") {
                jq.ajax({
                    dataType: "json",
                    url: VPOPC.option.URI,
                    data: "ctask=setdefaultsp&ajax=1" + e,
                    cache: false,
                    beforesend: function() {
                        jq("#proopc-order-submit").attr("disabled", "disabled")
                    },
                    success: function(e) {
                        if (e.error) {
                            jq(document).trigger("vpopc.event", ["checkout.defaultsp.failed"]);
                            jq("#proopc-order-submit").removeAttr("disabled");
                            console.log("Error: Setting default Shipment & Payment Method. Please select manually.")
                        } else {
                            jq(document).trigger("vpopc.event", ["checkout.defaultsp.success"]);
                            ProOPC.getcartlist()
                        }
                    },
                    error: function(e, t, n) {
                        console.log("Error: Setting default Shipment & Payment Method.");
                        console.log(e);
                        console.log(t);
                        console.log(n)
                    }
                })
            }
        },
        setshipment: function(e) {
            jq("#proopc-order-submit").attr("disabled", "disabled");
            if (jq(e).length > 0) {
                var t = jq(e).val();
                var n = "&virtuemart_shipmentmethod_id=" + t;
                var r = jq(e).data("usps");
                if (jq("#usps_name-" + t).length) {
                    var i = "usps_name-" + t + "=" + r.service;
                    n = n + "&" + i
                }
                if (jq("#usps_rate-" + t).length) {
                    var s = "usps_rate-" + t + "=" + r.rate;
                    n = n + "&" + s
                }
                var o = jq(e).data("ups");
                if (jq("#ups_rate-" + t).length) {
                    var u = "ups_rate-" + t + "=" + o.id;
                    n = n + "&" + u
                }
                if (ProOPC.ajaxPool.hasOwnProperty("setshipment") && ProOPC.ajaxPool["setshipment"].readystate != 4) {
                    ProOPC.ajaxPool["setshipment"].abort()
                }
                var shipments = [];
                jq('input[name="virtuemart_shipmentmethod_id"]').each(function() {
                	var id = jq(this).val();
                	if(!id) {
										return true;
									}
                	if(jq.inArray(id, shipments) >= 0) {
										n = '&' + jq('#proopc-shipment-form').serialize();
										return false;
									}
                	shipments.push(id);
                });
                if(jq(e).data('needfull')) {
									n = '&' + jq('#proopc-shipment-form').serialize();
								}
                ProOPC.ajaxPool["setshipment"] = jq.ajax({
                    dataType: "json",
                    url: VPOPC.option.URI,
                    data: "ctask=setshipments" + n,
                    cache: false,
                    success: function(e) {
                        if (e.error) {
                            ProOPC.setmsg("2", e.msg);
                            jq(document).trigger("vpopc.event", ["checkout.shipmentselection.failed", t])
                        } else {
                            jq(document).trigger("vpopc.event", ["checkout.shipmentselection.success", t]);
                            ProOPC.getcartlist();
                            jq(document).trigger("shipmentUpdate", [t]);
                            if (VPOPC.option.RELOADPAYMENTS == 1) {
                                ProOPC.getpayments()
                            }
                        }
                    },
                    error: function() {
                        console.log("Shipment Method selection problem (setshipment)");
                        jq("#proopc-order-submit").removeAttr("disabled")
                    }
                })
            }
        },
        setpayment: function(e) {
            jq("#proopc-order-submit").attr("disabled", "disabled");
            if (jq(e).is(":checked")) {
                var t = jq(e).data("pmtype");
                if (jq(e).data("paypalproduct")) {
                    t = t + jq(e).data("paypalproduct")
                }
                jq(".klarna_box_bottom").hide();
                jq(".vmpayment_cardinfo").removeClass("show").addClass("hide");
                jq(".vmpayment_cardinfo." + t).removeClass("hide").addClass("show")
            }
            if (jq(e).is(":checked") && jq(e).hasClass("klarnaPayment")) {
                ProOPC.klarnaOpenClose(e)
            }
            if (jq(e).length > 0) {
                var n = jq(e).val();
                var r = 0;
                var i = 0;
                if (jq(".vmpayment_cardinfo." + t).length > 0 || jq(e).data("pmtype") == "sisowideal") {
                    r = 1
                } else if (jq(e).hasClass("klarnaPayment")) {
                    r = 1 + "&" + jq("form#proopc-payment-form").serialize() + "&savecc=1";
                    i = 1
                }
                var s = "";
                if (jq(e).data("pmtype") == "piraeus") {
                    jq(".vmpayment_cardinfo.piraeus").find("select, input").each(function() {
                        s = s + "&" + jq(this).attr("name") + "=" + jq(this).val()
                    })
                }
                if (jq(e).data("paypalproduct") == "exp") {
                    ProOPC.setmsg(4, VPOPC.JText._("VMPAYMENT_PAYPAL_REDIRECT_MESSAGE"))
                }
                if (ProOPC.ajaxPool.hasOwnProperty("setpayment") && ProOPC.ajaxPool["setpayment"].readystate != 4) {
                    ProOPC.ajaxPool["setpayment"].abort()
                }
                ProOPC.ajaxPool["setpayment"] = jq.ajax({
                    dataType: "json",
                    url: VPOPC.option.URI,
                    data: "ctask=setpayment&ajax=1&virtuemart_paymentmethod_id=" + n + "&payment_data=" + r + s,
                    cache: false,
                    type: "post",
                    success: function(e) {
                        e.redirect = !e.redirect ? 0 : e.redirect;
                        if (e.error) {
                            ProOPC.setmsg("2", e.msg);
                            jq(document).trigger("vpopc.event", ["checkout.paymentselection.failed", n]);
                            ProOPC.getcartlist()
                        } else if (e.redirect) {
                            jq(document).trigger("vpopc.event", ["checkout.paymentselection.successs", n]);
                            ProOPC.getcartlist(e.redirect);
                            jq(document).trigger("paymentUpdate", [n])
                        } else {
                            if (e.msg && i) {
                                ProOPC.setmsg("2", e.msg)
                            }
                            jq(document).trigger("vpopc.event", ["checkout.paymentselection.successs", n]);
                            ProOPC.getcartlist();
                            jq(document).trigger("paymentUpdate", [n])
                        }
                    },
                    error: function(e, t, n) {
                        console.log("Payment Method selection problem (setpayment)");
                        jq("#proopc-order-submit").removeAttr("disabled")
                    }
                })
            }
        },
        getcartlist: function(e) {
            ProOPC.addloader("#proopc-pricelist");
            jq("#proopc-system-message").html("");
            if (ProOPC.ajaxPool.hasOwnProperty("getcartlist") && ProOPC.ajaxPool["getcartlist"].readystate != 4) {
                ProOPC.ajaxPool["getcartlist"].abort()
            }
            ProOPC.ajaxPool["getcartlist"] = jq.ajax({
                dataType: "json",
                url: VPOPC.option.URI,
                data: "ctask=getcartlist",
                cache: false,
                success: function(e) {
                		if(e.msg) {
											ProOPC.setmsg(e.msg_type, e.msg);
										}
                    jq("#proopc-pricelist").html(e.cartlist)
                },
                complete: function() {
                    ProOPC.style();
                    if (typeof e !== "undefined") {
                        window.location = e
                    }
                    ProOPC.removeloader("#proopc-pricelist")
                },
                error: function(e, t, n) {
                    console.log("Carlist Error: Error getting Cartlist Data (getcartlist).");
                    console.log(e);
                    console.log(t);
                    console.log(n)
                }
            })
        },
        getpayments: function() {
            ProOPC.addloader("#proopc-payments");
            if (ProOPC.ajaxPool.hasOwnProperty("getpayments") && ProOPC.ajaxPool["getpayments"].readystate != 4) {
                ProOPC.ajaxPool["getpayments"].abort()
            }
            ProOPC.ajaxPool["getpayments"] = jq.ajax({
                dataType: "json",
                url: VPOPC.option.URI,
                data: "ctask=getpaymentlist",
                cache: false,
                success: function(e) {
                    jq("#proopc-payments").html(e.payments);
                    if (e.payment_scripts.length > 0 && e.payment_scripts[0] !== "") {
                        payment_scripts = e.payment_scripts
                    }
                    if (e.payment_script[0] !== "") {
                        jq.each(e.payment_script, function(e, t) {
                            jq.getScript(t, function(e, t, n) {
                                if (typeof payment_scripts !== "undefined") {
                                    jq.each(payment_scripts, function(e, t) {
                                        t
                                    })
                                }
                            })
                        })
                    }
                },
                complete: function() {
                    ProOPC.style();
                    ProOPC.tooltip();
                    ProOPC.removeloader("#proopc-payments");
                    ProOPC.defaultSP()
                },
                error: function() {
                    console.log("Error: Error gettings Payments (getpayments).")
                }
            })
        },
        deleteproduct: function(e) {
            var t = jq(e).attr("data-vpid");
            jq.ajax({
                beforeSend: function() {
                    ProOPC.addloader("#proopc-pricelist, #proopc-payments, #proopc-shipments")
                },
                type: "POST",
                url: VPOPC.option.URI,
                data: "ctask=deleteproduct&id=" + t,
                cache: false,
                success: function(e) {
                    jq(document).trigger("vpopc.event", ["checkout.products.deleted"]);
                    jq("#proopc-system-message").html("");
                    jq(".proopc-product-hover").addClass("hide");
                    if (e.pqty == 0) {
                        window.location.reload();
                        return false
                    }
                    if (jq("input#proopc-cart-summery").length > 0) {
                        jq(document).trigger("productDelete", [t]);
                        ProOPC.getcartsummery();
                        jq("#proopc-cart-totalqty").text(e.pqty)
                    } else {
                        jq(document).trigger("productDelete", [t]);
                        ProOPC.getshipmentpaymentcartlist();
                        if (jq("#proopc-cart-totalqty").length > 0) {
                            jq("#proopc-cart-totalqty").text(e.pqty)
                        }
                    }
                    jq("body").trigger("updateVirtueMartCartModule")
                }
            });
            return false
        },
        updateproductqty: function(e) {
            ProOPC.addloader("#proopc-pricelist, #proopc-payments, #proopc-shipments");
            var t = false;
            jq(".proopc-qty-input").each(function() {
                if (jq(this).data("quantity") != jq(this).val()) {
                    t = true;
                    return false
                }
            });
            if (!t) {
                setTimeout(function() {
                    ProOPC.removeloader("#proopc-pricelist, #proopc-payments, #proopc-shipments")
                }, 300);
                return false
            }
            jq.ajax({
            	  type: "POST",
                dataType: "JSON",
                url: VPOPC.option.URI,
                data: jq("#proopc-carttable-form").serialize(),
                cache: false,
                success: function(e) {
                    if (e.error !== 0) {
                        jq(document).trigger("vpopc.event", ["checkout.products.updated", e.pqty]);
                        if (e.pqty == 0) {
                            window.location.reload();
                            return false
                        }
                        ProOPC.setmsg("1", e.msg);
                        if (jq("input#proopc-cart-summery").length > 0) {
                            jq("body").trigger("updateVirtueMartCartModule");
                            ProOPC.getcartsummery()
                        } else {
                            jq("body").trigger("updateVirtueMartCartModule");
                            ProOPC.getshipmentpaymentcartlist()
                        }
                    } else {
                        jq("#proopc-system-message").html("");
                        jq(".proopc-product-hover").addClass("hide");
                        if (jq("input#proopc-cart-summery").length > 0) {
                            ProOPC.getcartsummery();
                            jq("#proopc-cart-totalqty").text(e.pqty)
                        } else {
                            ProOPC.getshipmentpaymentcartlist();
                            jq("#proopc-cart-totalqty").text(e.pqty)
                        }
                        jq("body").trigger("updateVirtueMartCartModule")
                    }
                },
                error: function(e, t, n) {
                    console.log(e);
                    console.log(t);
                    console.log(n)
                }
            });
            return false
        },
        getcartsummery: function() {
            ProOPC.addloader("#proopc-pricelist");
            if (ProOPC.ajaxPool.hasOwnProperty("getcartsummery") && ProOPC.ajaxPool["getcartsummery"].readystate != 4) {
                ProOPC.ajaxPool["getcartsummery"].abort()
            }
            ProOPC.ajaxPool["getcartsummery"] = jq.ajax({
                dataType: "json",
                url: VPOPC.option.URI,
                data: "ctask=getcartsummery",
                cache: false,
                success: function(e) {
                    jq("#proopc-cart-qty").text(e.pqty);
                    jq("#proopc-pricelist").html(e.cartsummery)
                },
                complete: function() {
                    ProOPC.style();
                    ProOPC.removeloader("#proopc-pricelist")
                },
                error: function() {
                    console.log("Carlist Error: Error getting Cartlist Data (getcartlist).")
                }
            })
        },
        inputwidth: function() {
            if (VPOPC.option.GROUPING) {
                if (jq(".title-group").length && jq(".first_name-group").length) jq(".title-group, .first_name-group").wrapAll('<div class="proopc-row group-enabled" />');
                if (jq(".middle_name-group").length && jq(".last_name-group").length) jq(".middle_name-group, .last_name-group").wrapAll('<div class="proopc-row group-enabled" />');
                if (jq(".zip-group").length && jq(".city-group").length) jq(".zip-group, .city-group").wrapAll('<div class="proopc-row group-enabled" />');
                if (jq(".shipto_middle_name-group").length && jq(".shipto_last_name-group").length) jq(".shipto_middle_name-group, .shipto_last_name-group").wrapAll('<div class="proopc-row group-enabled" />');
                if (jq(".shipto_zip-group").length && jq(".shipto_city-group").length) jq(".shipto_zip-group, .shipto_city-group").wrapAll('<div class="proopc-row group-enabled" />')
            }
            jq('.proopc-bt-address input[type="text"], .proopc-st-address input[type="text"]').each(function() {
                var e = jq(this).parent(".inner").width();
                jq(this).width(e - 15)
            });
            jq('.proopc-register-login input[type="text"], .proopc-register-login input[type="password"]').each(function() {
                var e = jq(this).parent(".proopc-input").width();
                jq(this).width(e - 27)
            });
            jq(".proopc-register-login button").each(function() {
                var e = jq(this).parent(".proopc-input").outerWidth(true);
                jq(this).width(e)
            });
            var e = jq("#proopc-coupon .proopc-input-append").width();
            var t = jq("#proopc-coupon").find("button.proopc-btn").outerWidth(true);
            jq("#proopc-coupon-code").width(e - t - 20).css("margin-right", 5)
        },
        selectwidth: function() {
            jq(".proopc-bt-address select, .proopc-st-address select").each(function() {
                var e = jq(this).parent(".inner").width();
                jq(this).outerWidth(e - 3)
            });
            jq(".proopc-register-login select").each(function() {
                var e = jq(this).parent(".proopc-input").width();
                jq(this).outerWidth(e - 27)
            })
        },
        productdetails: function() {
            var e = {
                interval: 100,
                sensitivity: 4,
                over: ProOPC.openproductdetails,
                timeout: 200,
                out: ProOPC.closeproductdetails
            };
            jq(".proopc-cart-product").hoverIntent(e)
        },
        openproductdetails: function() {
            var e = jq(this).attr("data-details");
            jq(this).addClass("open");
            var t = jq(this).width();
            jq(this).find(".proopc-p-info-table").width(t);
            var n = jq(this).position().top;
            var r = jq(this).height();
            var i = jQuery.easing["jswing"] || jQuery.easing["jswing"] != "undefined" ? "easeOutExpo" : "linear";
            jq("#" + e).show().animate({
                opacity: 1,
                top: n + r
            }, 300, i)
        },
        closeproductdetails: function() {
            var e = jq(this).attr("data-details");
            jq(this).removeClass("open");
            var t = jQuery.easing["jswing"] || jQuery.easing["jswing"] != "undefined" ? "easeOutExpo" : "linear";
            jq("#" + e).animate({
                opacity: 0,
                top: 0
            }, 300, t, function() {
                jq(this).hide()
            })
        },
        savecoupon: function(e) {
            var t = jq("#proopc-coupon-code");
            var n = t.val();
            var r = t.attr("data-default");
            jq("#proopc-system-message").html("");
            if (n == r) {
                ProOPC.setmsg(1, VPOPC.JText._("PLG_VPONEPAGECHECKOUT_COUPON_EMPTY"))
            } else {
                var i = "ctask=setcoupon&coupon_code=" + encodeURIComponent(n);
                jq.ajax({
                    beforeSend: function() {
                        jq("#proopc-order-submit").attr("disabled", "disabled");
                        ProOPC.addloader("#proopc-coupon");
                        jq("#proopc-coupon-process").append(proopc_spinner.el)
                    },
                    dataType: "JSON",
                    url: VPOPC.option.URI,
                    data: i,
                    cache: false,
                    success: function(e) {
                        jq(document).trigger("vpopc.event", ["checkout.coupon.submitted", n, e.msg]);
                        e.error = !e.error ? "success" : e.error;
                        t.val(r);
                        if(!e.wait) {
													ProOPC.setmsg(e.error, e.msg);
												}
                        if (VPOPC.option.RELOADALLFORCOUPON == 1) {
                            ProOPC.getshipmentpaymentcartlist()
                        } else {
                            ProOPC.getcartlist()
                        }
                    },
                    error: function() {
                        ProOPC.removeloader("#proopc-coupon");
                        ProOPC.setmsg(1, "Coupon Error: Data could not be fetched.")
                    },
                    complete: function() {
                        ProOPC.removeloader("#proopc-coupon")
                    }
                })
            }
            return false
        },
        setmsg: function(e, t, n) {
            n = typeof n === "undefined" || typeof n === undefined ? true : n;
            if (e == "1" || e == "error") {
                var r = '<div class="proopc-alert proopc-error-msg"><button type="button" class="close" onclick="ProOPC.close(this);">x</button>' + t + "</div>"
            } else if (e == "2" || e == "warning") {
                var r = '<div class="proopc-alert proopc-warning-msg"><button type="button" class="close" onclick="ProOPC.close(this);">x</button>' + t + "</div>"
            } else if (e == "3" || e == "success") {
                var r = '<div class="proopc-alert proopc-success-msg"><button type="button" class="close" onclick="ProOPC.close(this);">x</button>' + t + "</div>"
            } else if (e == "4" || e == "info") {
                var r = '<div class="proopc-alert proopc-info-msg"><button type="button" class="close" onclick="ProOPC.close(this);">x</button>' + t + "</div>"
            } else {
                var r = '<div class="proopc-alert"><button type="button" class="close" onclick="ProOPC.close(this);">x</button>' + t + "</div>"
            }
            jq("#proopc-system-message").html(r);
            if (n) {
                jq("html,body").animate({
                    scrollTop: jq("#proopc-system-message").offset().top - 100
                }, 500)
            }
        },
        addloader: function(e) {
            var t = '<div class="proopc-loader-overlay"></div><div class="proopc-area-loader"><span></span></div>';
            jq(e).each(function() {
                if (jq(this).find(".proopc-area-loader").length == 0) {
                    jq(this).append(t);
                    jq(".proopc-area-loader > span").append(proopc_area_loader.el)
                }
            });
            jq("#header .navigation.sticky").css("z-index", 2e9);
            jq("#proopc-order-submit").attr("disabled", "disabled")
        },
        removeloader: function(e) {
            jq(e).each(function() {
                if (jq(this).find(".proopc-loader-overlay").length > 0) {
                    jq(this).find(".proopc-loader-overlay").remove();
                    jq(this).find(".proopc-area-loader").remove()
                }
            });
            jq("#header .navigation.sticky").css("z-index", "");
            jq("#proopc-order-submit").removeAttr("disabled")
        },
        addpageloader: function() {
            if (jq("#proopc-page-overlay").length == 0) {
                jq("body").append('<div id="proopc-page-overlay"></div><div id="proopc-page-spinner"><span></span></div>')
            }
            jq("#header .navigation.sticky").css("z-index", 2e9);
            var e = jq("body").outerHeight();
            jq("#proopc-page-overlay").css({
                display: "block",
                height: e
            }).animate({
                opacity: .7
            }, 300);
            jq("#proopc-page-spinner > span").append(proopc_loader.el)
        },
        removepageloader: function() {
            if (jq("#proopc-page-overlay").length > 0) {
                jq("#proopc-page-overlay, #proopc-page-spinner").remove()
            }
            jq("#header .navigation.sticky").css("z-index", "")
        },
        tooltip: function() {
            jq(".hover-tootip").hover(function() {
                var e = jq(this).attr("title");
                jq(this).data("tipText", e).removeAttr("title");
                if (e.indexOf("::") >= 0) {
                    var e = e.split("::");
                    var t = '<div class="tooltip-title">' + e[0] + '</div><div class="tooltip-body">' + e[1] + "</div>";
                    jq('<p class="proopc-tooltip"></p>').html(t).appendTo("body").fadeIn("slow")
                } else {
                    var t = '<div class="tooltip-body">' + e + "</div>";
                    jq('<p class="proopc-tooltip"></p>').html(t).appendTo("body").fadeIn("slow")
                }
            }, function() {
                jq(this).attr("title", jq(this).data("tipText"));
                jq(".proopc-tooltip").remove()
            }).mousemove(function(e) {
                var t = e.pageX + 20;
                var n = e.pageY + 10;
                jq(".proopc-tooltip").css({
                    top: n,
                    left: t
                })
            })
        },
        fieldTip: function() {
            jq(".hasFieldTip").hover(function() {
                if (!jq(this).attr("title")) return;
                ProOPC.showFieldTip(this)
            }, function() {
                ProOPC.removeFieldTip(this, true)
            })
        },
        showFieldTip: function(e) {
            var t = jq(e);
            if (t.data("tip")) {
                return
            }
            var n = "field-tip-" + jq.now();
            var r = t.attr("title");
            var i = t.outerWidth();
            var s = t.offset();
            var o = jq('<p class="proopc-fieldtip"></p>').attr("id", n);
            t.data("tipText", r).attr("title", "").data("tip", "#" + n);
            var u = '<div class="fieldtip-body">' + r + "</div>";
            o.html(u).css("visibility", "hidden").appendTo("body");
            var a = o.outerWidth();
            var f = o.outerHeight() + 6;
            var l = s.top - f;
            var c = s.left + (i - a) / 2;
            o.css({
                top: l,
                left: c,
                visibility: ""
            }).fadeIn(200);
            jq(window).resize(function() {
                var e;
                if (e) {
                    clearTimeout(e)
                }
                setTimeout(function() {
                    i = t.outerWidth();
                    s = t.offset();
                    a = o.outerWidth();
                    f = o.outerHeight() + 6;
                    l = s.top - f;
                    c = s.left + (i - a) / 2;
                    o.css({
                        top: l,
                        left: c
                    })
                }, 200)
            })
        },
        removeFieldTip: function(e, t) {
            var n = jq(e);
            var r = n.data("tip");
            if (!r || !jq(r).length) {
                return
            }
            n.data("tip", null);
            if (t) {
                n.attr("title", n.data("tipText"))
            }
            jq(r).animate({
                opacity: 0
            }, 200, function() {
                jq(this).remove()
            })
        },
        style: function() {
            var e = 0;
            jq(".proopc-register > .proopc-inner, .proopc-login > .proopc-inner").css("min-height", "");
            jq(".proopc-register > .proopc-inner, .proopc-login > .proopc-inner").each(function() {
                if (jq(this).height() > e) {
                    e = jq(this).height()
                }
            });
            jq(".proopc-register > .proopc-inner, .proopc-login > .proopc-inner").css("min-height", e);
            if (jq("#ProOPC").find("#dynamic_recaptcha_1").length) {
                var t = jq("#ProOPC #dynamic_recaptcha_1").find("table");
                var n = t.width();
                jq(t).parents(".proopc-input").width(n);
                var r = jq(".proopc-register > .proopc-inner").width() - 35;
                jq(t).parents(".proopc-input").siblings(".proopc-input-group-level").width(r - n)
            }
            var i, s;
            jq("table.cart-summary.proopc-table-striped td").each(function() {
                i = jq(this).text();
                i = i ? jq.trim(i) : "";
                s = jq(this).html();
                s = s ? jq.trim(s) : "";
                if (!i.length && !s.length || jq(this).is(":empty")) {
                    jq(this).addClass("cell-empty")
                }
            });
            jq("table.cart-summary.proopc-table-striped span.vm-price-desc").each(function() {
                i = jq(this).text();
                i = i ? jq.trim(i) : "";
                s = jq(this).html();
                s = s ? jq.trim(s) : "";
                if (!i.length && !s.length || jq(this).is(":empty")) {
                    jq(this).hide()
                }
            });
            jq(".proopc-p-price > div, .proopc-taxcomponent > div, .proopc-p-discount > div").each(function() {
                if (jq(this).is(":visible")) {
                    jq(this).css("display", "inline")
                }
            });
            jq(".proopc-login-message-cont").hover(function() {
                jq(".proopc-logout-cont").removeClass("hide")
            }, function() {
                jq(".proopc-logout-cont").addClass("hide")
            });
            jq('[data-vpopc="redirect"]').click(function(e) {
                e.preventDefault();
                var t = jq(this).attr("data-vphref");
                if (t) {
                    window.location.href = t;
                    return false
                }
            });
            jq(".proopc-logout-cont").css('min-width', jq(".proopc-loggedin-user").width());
            if (typeof window.bonusCartItemIds === "undefined") {} else {
                jq.each(window.bonusCartItemIds, function(e, t) {
                    if (!t.userCanUpdateQuantity) {
                        updateform = jq('input[data-vpid="' + t.cartItemId + '"]').parent(".proopc-input-append");
                        var n = jq(updateform).children('input[name="quantity"]').val();
                        if (!jq(updateform).hasClass("bonusSet")) {
                            updateform.before(n);
                            jq(updateform).addClass("bonusSet")
                        }
                        updateform.hide();
                        updateform.parent().find("button.remove_from_cart").hide()
                    }
                })
            }
            if (VPOPC.option.STYLERADIOCHEBOX == 1) {
                jq('#UserRegistration input[type="radio"], #EditBTAddres input[type="radio"], #EditSTAddres input[type="radio"]').each(function() {
                    jq(this).css("float", "left");
                    jq('label[for="' + jq(this).attr("id") + '"]').addClass("proopc-radio-label")
                });
                jq('#UserRegistration input[type="checkbox"], #EditBTAddres input[type="checkbox"], #EditSTAddres input[type="checkbox"]').each(function() {
                    jq(this).css({
                        "float": "left",
                        margin: "4px 5px 0 0"
                    });
                    jq(this).parent(".proopc-input-append").css("padding-top", "4px");
                    jq(this).siblings("br").remove();
                    jq('label[for="' + jq(this).attr("id") + '"]').css({
                        "float": "left",
                        "padding-left": "10px",
                        "padding-right": "10px"
                    }).insertAfter(this)
                })
            }
            jq(".proopc-creditcard-info").each(function() {
                if (jq(this).parent(".vmpayment_cardinfo").length == 0) {
                    var e = jq(this).prevAll('input[name="virtuemart_paymentmethod_id"]');
                    var t = jq(e).attr("id");
                    var n = jq(e).data("pmtype");
                    if (jq(e).data("paypalproduct")) {
                        n = n + jq(e).data("paypalproduct")
                    }
                    var r = "hide";
                    if (jq("#" + t).is(":checked")) {
                        r = "show"
                    }
                    jq(this).wrap('<span class="vmpayment_cardinfo additional-payment-info ' + n + " " + r + '">')
                }
            });
            if (typeof klarna === "undefined") {
                klarnaloadneeded = false
            } else {
                klarnaloadneeded = true
            }
            if (klarnaloadneeded) {
                ProOPC.callKlarna()
            }
            jq("form#proopc-shipment-form").find("select").change(function() {
                jq.ajax({
                    dataType: "json",
                    url: VPOPC.option.URI,
                    data: "ctask=setshipments&" + jq("form#proopc-shipment-form").serialize(),
                    cache: false,
                    success: function(e) {
                        if (e.error) {
                            var t = '<div class="proopc-alert proopc-success-msg"><button type="button" class="close" onclick="ProOPC.close(this);">x</button>' + e.msg + "</div>";
                            jq("#proopc-system-message").html(t);
                            jq("html,body").animate({
                                scrollTop: jq("#proopc-system-message").offset().top - 100
                            }, 500)
                        } else {
                            jq(document).trigger("shipmentUpdate", [jq("form#proopc-shipment-form")]);
                            ProOPC.getcartlist()
                        }
                    }
                })
            });
            jq("form#proopc-shipment-form").find("select").each(function() {
                jq(this).width(jq(this).parents("fieldset").width())
            });
            jq("form#proopc-payment-form").find("select").change(function() {
                if (jq('#proopc-payment-form input[type="radio"][name="virtuemart_paymentmethod_id"]:checked').data("pmtype") == "sisowideal" || jq('#proopc-payment-form input[type="radio"][name="virtuemart_paymentmethod_id"]:checked').data("pmtype") == "buckaroo") {
                    var e = jq("form#proopc-payment-form").serialize();
                    var t = "ctask=setpayment&savecc=1&payment_data=1";
                    if (jq('#proopc-payment-form input[type="radio"][name="virtuemart_paymentmethod_id"]:checked').data("pmtype") == "piraeus") {
                        t = "ctask=setpayment&payment_data=1"
                    }
                    jq.ajax({
                        dataType: "json",
                        url: VPOPC.option.URI,
                        data: t + "&" + e,
                        cache: false,
                        success: function(e) {
                            if (e.error) {
                                var t = '<div class="proopc-alert proopc-success-msg"><button type="button" class="close" onclick="ProOPC.close(this);">x</button>' + e.msg + "</div>";
                                jq("#proopc-system-message").html(t);
                                jq("html,body").animate({
                                    scrollTop: jq("#proopc-system-message").offset().top - 100
                                }, 500)
                            } else {
                                jq(document).trigger("paymentUpdate", [jq("form#proopc-payment-form")]);
                                ProOPC.getcartlist()
                            }
                        }
                    })
                } else if (jq('#proopc-payment-form input[type="radio"][name="virtuemart_paymentmethod_id"]:checked').data("pmtype") == "piraeus") {
                    ProOPC.setpayment(jq('#proopc-payment-form input[type="radio"][name="virtuemart_paymentmethod_id"]:checked'))
                }
            });
            jq("#sisow_bank").width(jq("#sisow_bank").parent("fieldset").width());
            jq("#monthinstallments").width(jq("#monthinstallments").parents(".proopc-row").width());
            jq(".cc_type_sandbox").change(function() {
                var e = jq(this).attr("rel");
                var t = jq("#cc_type_" + e).val();
                switch (t) {
                    case "Visa":
                        jq("#cc_number_" + e).val("4007000000027");
                        jq("#cc_cvv_" + e).val("123");
                        break;
                    case "Mastercard":
                        jq("#cc_number_" + e).val("6011000000000012");
                        jq("#cc_cvv_" + e).val("123");
                        break;
                    case "Amex":
                        jq("#cc_number_" + e).val("370000000000002");
                        jq("#cc_cvv_" + e).val("1234");
                        break;
                    case "Discover":
                        jq("#cc_number_" + e).val("5424000000000015");
                        jq("#cc_cvv_" + e).val("123");
                        break;
                    case "Maestro":
                        jq("#cc_number_" + e).val("6763318282526706");
                        jq("#cc_cvv_" + e).val("123");
                        break;
                    default:
                        jq("#cc_number_" + e).val("");
                        jq("#cc_cvv_" + e).val("")
                }
            });
            jq(".cc_type_sandbox").trigger("change");
            var o = jq(".vmpayment_cardinfo");
            if (o.parent().is("div")) {
                o.unwrap()
            }
            if (VPOPC.option.REMOVEUNNECESSARYLINKS == 1) {
                jq("span.vmpayment").find("a").each(function() {
                    var e = jq(this).text();
                    jq(this).parents("label").siblings(".vmpayment_cardinfo").prepend('<div class="proopc-payment-text">' + e + "</div>");
                    jq(this).remove()
                });
                jq("#ProOPC").find('a[href="' + VPOPC.option.EDITPAYMENTURI + '"]').remove()
            }
            if (VPOPC.option.TOSFANCY) {
                var u = jq("div#proopc-tos-fancy"),
                    a = u.parent(),
                    f = jQuery.easing["jswing"] || jQuery.easing["jswing"] != "undefined" ? "elastic" : "fade";
                jq('[data-tos="fancybox"]').fancybox({
                    titlePosition: "inside",
                    padding: 0,
                    showCloseButton: false,
                    centerOnScroll: true,
                    transitionIn: "fade",
                    transitionOut: f,
                    overlayOpacity: .8,
                    overlayColor: "#000",
                    onClosed: function() {
                        if (!jq(this.href).length) {
                            a.html(u);
                            jq("button.fancy-close", u).click(function(e) {
                                e.preventDefault();
                                parent.jq.fancybox.close()
                            })
                        }
                    }
                });
                jq("div#proopc-tos-fancy button.fancy-close").click(function(e) {
                    e.preventDefault();
                    parent.jq.fancybox.close()
                })
            }
            if (VPOPC.option.RELOAD && !jq("#ProOPC").hasClass("loaded") && jq(".proopc-reload").length) {
                jq("html,body").animate({
                    scrollTop: jq("#proopc-system-message").offset().top - 100
                }, 500, function() {
                    jq("#ProOPC").addClass("loaded")
                })
            }
            if (jq("input.klarnaPayment:radio").length) {
                jq("input.klarnaPayment:radio").attr("onclick", "return ProOPC.setpayment(this);");
                jq("input.klarnaPayment:radio").parents("table").addClass("proopc-klarna-payment");
                var n = jq("#proopc-payment-form").find('input:radio[name="virtuemart_paymentmethod_id"]:checked');
                ProOPC.klarnaOpenClose(n)
            }
        },
        close: function(e) {
            jq(e).parent(".proopc-alert").remove()
        },
        validateForm: function(e) {
            var t = VPOPC.JText._("PLG_VPONEPAGECHECKOUT_REQUIRED_FIELDS_MISSING");
            var n = true;
            jq(e + ' input[type="text"]:not(#proopc-secretkey),' + e + ' input[type="password"],' + e + " input.required," + e + " select.required").each(function() {
                jq('label[for="' + jq(this).attr("id") + '"]').removeClass("invalid");
                if (jq(this).val() == "" || jq(this).hasClass("invalid")) {
                    jq(this).siblings(".status").removeClass("valid").addClass("invalid").attr("title", VPOPC.JText._("PLG_VPONEPAGECHECKOUT_REQUIRED_FIELD"));
                    n = false
                } else if (jq(this).attr("type") == "checkbox" && jq(this).is(":checked") == false) {
                    jq('label[for="' + jq(this).attr("id") + '"]').addClass("invalid");
                    n = false
                } else if (jq(this).attr("type") == "radio" && !jq('input[name="' + jq(this).attr("name") + '"]:checked').val()) {
                    jq('label[for="' + jq(this).attr("id") + '"]').addClass("invalid");
                    n = false
                }
            });
            if (n == false) {
                var r = '<div class="proopc-alert proopc-error-msg"><button type="button" class="close" onclick="ProOPC.close(this);">&times;</button>' + t + "</div>";
                jq("#proopc-system-message").html(r);
                jq("html,body").animate({
                    scrollTop: jq("#proopc-system-message").offset().top - 100
                }, 500)
            }
            return n
        },
        submitOrder: function() {
            jq("#proopc-system-message").html("");
            var e = false;
            var t = jq('label[for="virtuemart_state_id"]').text();
            var n = t && t.length > 0 && t.indexOf("*") >= 0;
            if (n && jq("#virtuemart_state_id optgroup").length > 0 && jq("#virtuemart_state_id").val() == "") {
                jq("#virtuemart_state_id").addClass("required")
            }
            t = jq('label[for="shipto_virtuemart_state_id"]').text();
            n = t && t.length > 0 && t.indexOf("*") >= 0;
            if (n && jq("#shipto_virtuemart_state_id optgroup").length > 0 && jq("#shipto_virtuemart_state_id").val() == "") {
                jq("#shipto_virtuemart_state_id").addClass("required")
            }
            jq("#EditBTAddres select, #EditBTAddres input").each(function() {
                if (jq(this).hasClass("required") && jq(this).val() == "") {
                    jq(this).addClass("invalid");
                    var t = jq(this).attr("id");
                    jq('label[for="' + t + '"]').addClass("invalid");
                    e = true
                } else if (jq(this).attr("name") == "email" || jq(this).attr("name") == "shipto_email") {
                    if (!ProOPC.validateEmail(jq(this).val())) {
                        jq(this).addClass("invalid");
                        var t = jq(this).attr("id");
                        jq('label[for="' + t + '"]').addClass("invalid");
                        e = true
                    }
                }
            });
            jq("#EditBTAddres select, #EditBTAddres input").change(function() {
                if (jq(this).hasClass("invalid") && jq(this).val() !== "") {
                    jq(this).removeClass("invalid");
                    var e = jq(this).attr("id");
                    jq('label[for="' + e + '"]').removeClass("invalid")
                }
            });
            if (jq("#STsameAsBT").is(":checked")) {
                jq("#EditBTAddres input").each(function() {
                    var e = jq(this).attr("id");
                    jq("#shipto_" + e).val(jq(this).val())
                })
            } else {
                jq("#EditSTAddres select, #EditSTAddres input").each(function() {
                    if (jq(this).hasClass("required") && jq(this).val() == "") {
                        jq(this).addClass("invalid");
                        var t = jq(this).attr("id");
                        jq('label[for="' + t + '"]').addClass("invalid");
                        e = true
                    }
                });
                jq("#EditSTAddres select, #EditSTAddres input").change(function() {
                    if (jq(this).hasClass("invalid") && jq(this).val() !== "") {
                        jq(this).removeClass("invalid");
                        var e = jq(this).attr("id");
                        jq('label[for="' + e + '"]').removeClass("invalid")
                    }
                })
            }
            if (e) {
                var r = '<div class="proopc-alert proopc-error-msg"><button type="button" class="close" onclick="ProOPC.close(this);">x</button>' + VPOPC.JText._("COM_VIRTUEMART_USER_FORM_MISSING_REQUIRED_JS") + "</div>";
                jq("#proopc-system-message").html(r);
                jq("html,body").animate({
                    scrollTop: jq("#proopc-system-message").offset().top - 100
                }, 500);
                return false
            }
            if (jq('#proopc-shipments input[name="virtuemart_shipmentmethod_id"]').is(":checked") == false) {
                var r = '<div class="proopc-alert proopc-error-msg"><button type="button" class="close" onclick="ProOPC.close(this);">x</button>' + VPOPC.JText._("COM_VIRTUEMART_CART_NO_SHIPMENT_SELECTED") + "</div>";
                jq("#proopc-system-message").html(r);
                jq("html,body").animate({
                    scrollTop: jq("#proopc-system-message").offset().top - 100
                }, 500);
                return false
            }
            if (jq('#proopc-payments input[name="virtuemart_paymentmethod_id"]').is(":checked") == false) {
                var r = '<div class="proopc-alert proopc-error-msg"><button type="button" class="close" onclick="ProOPC.close(this);">x</button>' + VPOPC.JText._("COM_VIRTUEMART_CART_NO_PAYMENT_SELECTED") + "</div>";
                jq("#proopc-system-message").html(r);
                jq("html,body").animate({
                    scrollTop: jq("#proopc-system-message").offset().top - 100
                }, 500);
                return false
            }
            var i = jq("#proopc-payment-form").find("input:radio[name=virtuemart_paymentmethod_id]:checked");
            if (i.hasClass("klarnaPayment")) {
                var s = false;
                var o = [];
                var u = i.parents("table");
                jq(u).find("input, select, textarea, .klarna_box_bottom_title").removeClass("invalid");
                jq(u).find("input:visible").not(":checkbox").each(function() {
                    if (jq(this).val() == "") {
                        jq(this).addClass("invalid");
                        jq(this).prev(".klarna_box_bottom_title").addClass("invalid");
                        o.push(jq(this).prev(".klarna_box_bottom_title").text());
                        s = true
                    }
                });
                var a = false;
                jq(u).find("select").each(function() {
                    if (parseInt(jq(this).val()) == "" || parseInt(jq(this).val()) == 0 || isNaN(parseInt(jq(this).val()))) {
                        jq(this).addClass("invalid");
                        jq(this).parents(".klarna_box_bottom_input_combo").prev(".klarna_box_bottom_title").addClass("invalid");
                        if (a == false) {
                            o.push(jq(this).parents(".klarna_box_bottom_input_combo").prev(".klarna_box_bottom_title").text());
                            a = true
                        }
                        s = true
                    }
                });
                jq(u).find('input[type="checkbox"]').each(function() {
                    if (jq(this).is(":checked") == false) {
                        jq(this).addClass("invalid");
                        jq(this).next(".klarna_box_bottom_title").addClass("invalid");
                        o.push("Klarna: " + VPOPC.JText._("COM_VIRTUEMART_CART_PLEASE_ACCEPT_TOS"));
                        s = true
                    }
                });
                var f = "";
                if (jq(o).length > 0) {
                    f = VPOPC.JText._("COM_VIRTUEMART_USER_FORM_MISSING_REQUIRED_JS") + ": " + o.join(", ") + "."
                }
                jq(u).find("input, select, textarea").change(function() {
                    if (jq(this).is("input") && jq(this).val() != "") {
                        jq(this).removeClass("invalid");
                        jq(this).prev(".klarna_box_bottom_title").removeClass("invalid")
                    }
                    if (jq(this).is("select") && (jq(this).val() != "" || jq(this).val() != "0")) {
                        jq(this).removeClass("invalid");
                        jq(this).parents(".klarna_box_bottom_input_combo").prev(".klarna_box_bottom_title").removeClass("invalid")
                    }
                    if (jq(this).is("input:checkbox, input:radio") && jq(this).is(":checked")) {
                        jq(this).removeClass("invalid");
                        jq(this).next(".klarna_box_bottom_title").removeClass("invalid")
                    }
                })
            }
            if (s) {
                var r = '<div class="proopc-alert proopc-error-msg"><button type="button" class="close" onclick="ProOPC.close(this);">x</button>' + f + "</div>";
                jq("#proopc-system-message").html(r);
                jq("html,body").animate({
                    scrollTop: jq("#proopc-system-message").offset().top - 100
                }, 500);
                return false
            }
            if (VPOPC.option.VMCONFIGTOS || jq('input[name="tos"]:checkbox').length) {
                if (jq('input[name="tos"]:checkbox').is(":checked") == false) {
                    ProOPC.setmsg(1, VPOPC.JText._("COM_VIRTUEMART_CART_PLEASE_ACCEPT_TOS"));
                    return false
                }
            }
            var l = jq("#EditBTAddres").serialize();
            var c = jq("#checkoutForm").serialize();
            var h = jq("#formToken input").attr("name");
            var p = jq("#formToken input").val();
            var d = l + "&" + c + "&ctask=savebtaddress&stage=final&" + h + "=" + p;
            jq.ajax({
                beforeSend: function() {
                    ProOPC.addpageloader();
                    jq("#proopc-page-spinner").after('<div id="proopc-order-process"></div>');
                    jq("#proopc-order-process").text(VPOPC.JText._("PLG_VPONEPAGECHECKOUT_SAVING_BILLING_ADDRESS"));
                    jq("#proopc-order-submit").attr("disabled", "disabled")
                },
                dataType: "json",
                type: "POST",
                url: VPOPC.option.URI,
                data: d,
                cache: false,
                success: function(e) {
                    if (e.error == 1) {
                        ProOPC.removepageloader();
                        ProOPC.setmsg(1, e.msg);
                        jq("#proopc-order-submit").removeAttr("disabled");
                        return false
                    }
                    jq(document).trigger("vpopc.event", ["checkout.bt.saved"]);
                    jq("#proopc-order-process").text(VPOPC.JText._("PLG_VPONEPAGECHECKOUT_BILLING_ADDRESS_SAVED"));
                    if (jq("#STsameAsBT").is(":checked") == false) {
                        jq('#proopc-hidden-confirm input[name="STsameAsBT"]').val(0);
                        var t = jq("#EditSTAddres").serialize();
                        var n = jq("#formToken input").attr("name");
                        var r = jq("#formToken input").val();
                        var i = t + "&ctask=savestaddress&stage=final&" + n + "=" + r;
                        jq.ajax({
                            beforeSend: function() {
                                jq("#proopc-order-process").text(VPOPC.JText._("PLG_VPONEPAGECHECKOUT_SAVING_SHIPPING_ADDRESS"))
                            },
                            dataType: "json",
                            type: "POST",
                            async: false,
                            url: VPOPC.option.URI,
                            data: i,
                            success: function(e) {
                                if (e.error == 1) {
                                    ProOPC.removepageloader();
                                    ProOPC.setmsg(1, e.msg);
                                    jq("#proopc-order-submit").removeAttr("disabled");
                                    return false
                                }
                                jq(document).trigger("vpopc.event", ["checkout.st.saved"]);
                                jq('#proopc-hidden-confirm input[name="shipto"]').val(e.userinfo_id);
                                jq("#proopc-order-process").text(VPOPC.JText._("PLG_VPONEPAGECHECKOUT_SHIPPING_ADDRESS_SAVED"));
                                setTimeout(function() {
                                    ProOPC.saveCCdata()
                                }, 50)
                            }
                        })
                    } else {
                        jq('#proopc-hidden-confirm input[name="STsameAsBT"]').val(1);
                        jq('#proopc-hidden-confirm input[name="shipto"]').val(-1);
                        setTimeout(function() {
                            ProOPC.saveCCdata()
                        })
                    }
                }
            });
            return false
        },
        saveCartFields: function() {
            var e = jq("form#checkoutForm").serialize();
            jq.ajax({
                beforeSend: function() {
                    jq("#proopc-order-process").text(VPOPC.JText._("PLG_VPONEPAGECHECKOUT_SAVING_CART_FIELDS"))
                },
                dataType: "json",
                type: "POST",
                url: VPOPC.option.URI,
                async: false,
                data: "ctask=savecartfields&&" + e,
                cache: false,
                success: function(e, t, n) {
                    if (e.error) {
                        ProOPC.getcartlist();
                        ProOPC.removepageloader();
                        jq("#proopc-order-process").remove();
                        ProOPC.setmsg(1, e.msg);
                        jq("#proopc-order-submit").removeAttr("disabled");
                        return false
                    } else {
                        jq(document).trigger("vpopc.event", ["checkout.cartfields.saved"]);
                        jq("#proopc-order-process").text(VPOPC.JText._("PLG_VPONEPAGECHECKOUT_CART_FIELDS_SAVED"));
                        setTimeout(function() {
                            ProOPC.verifyCheckout()
                        }, 50);
                        return true
                    }
                },
                error: function(e, t, n) {
                    console.log(e);
                    console.log(t);
                    console.log(n)
                }
            })
        },
        saveCCdata: function() {
            var e = jq("form#proopc-payment-form").serialize();
            var t = jq("#proopc-payment-form").find("input:radio[name=virtuemart_paymentmethod_id]:checked");
            jq.ajax({
                beforeSend: function() {
                    jq("#proopc-order-process").text(VPOPC.JText._("PLG_VPONEPAGECHECKOUT_SAVING_CREDIT_CARD"))
                },
                dataType: "json",
                type: "POST",
                url: VPOPC.option.URI,
                async: false,
                data: "ctask=setpayment&ajax=1&savecc=1&" + e + "&payment_data=1",
                cache: false,
                success: function(e, t, n) {
                    if (n.getResponseHeader("content-type").indexOf("text/html") >= 0 && typeof klarna !== "undefined") {
                        ProOPC.removepageloader();
                        jq("#proopc-order-process").remove();
                        jq("<div/>", {
                            id: "proopc-temp",
                            style: "display:none"
                        }).appendTo("body");
                        jq("#proopc-temp").append(e);
                        var r = jq("#proopc-temp").find("div#system-message-container").html();
                        ProOPC.setmsg(1, r);
                        jq("#proopc-order-submit").removeAttr("disabled");
                        return false
                    } else {
                        if (typeof e === "string") {
                            e = jq.parseJSON(e)
                        }
                        if (e.error) {
                            ProOPC.getcartlist();
                            ProOPC.removepageloader();
                            jq("#proopc-order-process").remove();
                            ProOPC.setmsg(1, e.msg);
                            jq("#proopc-order-submit").removeAttr("disabled");
                            return false
                        } else if (e.redirect != false) {
                            window.location = e.redirect
                        } else {
                            jq(document).trigger("vpopc.event", ["checkout.paymentmethod.saved"]);
                            jq("#proopc-order-process").text(VPOPC.JText._("PLG_VPONEPAGECHECKOUT_CREDIT_CARD_SAVED"));
                            setTimeout(function() {
                                ProOPC.saveCartFields()
                            }, 50);
                            return true
                        }
                    }
                }
            })
        },
        saveAdditionalPaymentdata: function() {
            var e = jq("form#proopc-payment-form").serialize();
            jq("#proopc-order-submit").attr("disabled", "disabled");
            var t = jq("#proopc-payment-form").find("input:radio[name=virtuemart_paymentmethod_id]:checked");
            jq.ajax({
                dataType: "json",
                type: "POST",
                url: VPOPC.option.URI,
                async: false,
                data: "ctask=setpayment&ajax=1&savecc=1&" + e + "&payment_data=1",
                cache: false,
                success: function(e, t, n) {
                    if (n.getResponseHeader("content-type").indexOf("text/html") >= 0 && typeof klarna !== "undefined") {
                        jq("#proopc-order-submit").removeAttr("disabled");
                        jq("<div/>", {
                            id: "proopc-temp",
                            style: "display:none"
                        }).appendTo("body");
                        jq("#proopc-temp").append(e);
                        var r = jq("#proopc-temp").find("div#system-message-container").html();
                        ProOPC.setmsg(1, r);
                        jq("#proopc-order-submit").removeAttr("disabled")
                    } else {
                        if (typeof e === "string") {
                            e = jq.parseJSON(e)
                        }
                        if (e.error) {
                            ProOPC.getcartlist();
                            jq("#proopc-order-submit").removeAttr("disabled");
                            ProOPC.setmsg(1, e.msg)
                        } else if (e.redirect != false) {
                            window.location = e.redirect
                        } else {
                            jq(document).trigger("vpopc.event", ["checkout.paymentmethod.additionaldata.saved"]);
                            jq("#proopc-order-submit").removeAttr("disabled");
                            ProOPC.setmsg(4, VPOPC.JText._("PLG_VPONEPAGECHECKOUT_CREDIT_CARD_SAVED"))
                        }
                    }
                },
                error: function(e, t, n) {
                    console.log("error: saving additional payment details");
                    console.log(e);
                    console.log(t);
                    console.log(n)
                }
            });
            return false
        },
        verifyCheckout: function() {
            jq.ajax({
                beforeSend: function() {
                    jq("#proopc-order-process").text(VPOPC.JText._("PLG_VPONEPAGECHECKOUT_VERIFYING_ORDER"))
                },
                dataType: "json",
                type: "POST",
                async: false,
                cache: false,
                url: VPOPC.option.URI,
                data: "ctask=verifycheckout",
                success: function(e) {
                    console.log(e);
                    if (e.error == 0) {
                        jq(document).trigger("vpopc.event", ["checkout.orderVerification.success"]);
                        jq("#proopc-order-process").text(VPOPC.JText._("PLG_VPONEPAGECHECKOUT_PLACING_ORDER"));
                        jq("#checkoutForm").append(jq("#proopc-hidden-confirm").html());
                        setTimeout(function() {
                            jq("#checkoutForm").submit()
                        }, 50);
                        return false
                    } else {
                        jq(document).trigger("vpopc.event", ["checkout.orderVerification.failed"]);
                        ProOPC.removepageloader();
                        jq("#proopc-order-process").remove();
                        ProOPC.setmsg(1, e.msg);
                        jq("#proopc-order-submit").removeAttr("disabled");
                        return false
                    }
                },
                error: function() {
                    console.log("error")
                }
            })
        },
        callKlarna: function() {
            var e = jq("#klarna_baloon").clone();
            jq("body").find(".klarna_baloon").remove();
            var t = jq("#klarna_blue_baloon").clone();
            jq("body").find(".klarna_blue_baloon").remove();
            jq("body").append(e);
            jq("body").append(t);
            klarna.doDocumentIsReady(jq(".klarna_box"));
            jq(".klarna_box_bottom_languageInfo").remove();
            if (!klarna.unary_checkout) {
                var n = jq("#proopc-payment-form input[type=radio][name=virtuemart_paymentmethod_id]");
                ProOPC.initPaymentSelection(n.filter(":checked"))
            }
            klarna.baloons_moved = true
        },
        initPaymentSelection: function(e) {
            var t = e.hasClass("klarnaPayment");
            klarna.gChoice = "";
            klarna.stype = e.data("stype");
            if (t) {
                klarna.gChoice = e.attr("id")
            }
            var n = jQuery("#proopc-payment-form .klarnaPayment");
            ProOPC.klarnaOpenClose(e)
        },
        klarnaOpenClose: function(e) {
            var t = jq(e).hasClass("klarnaPayment");
            var n;
            var r = 0;
            var i;
            if (t) {
                n = jq(e).parents(".proopc-klarna-payment");
                n.siblings().find(".klarna_box_bottom:visible").hide();
                jq('.proopc-klarna-payment input[name="klarna_paymentmethod"]').attr("disabled", true);
                n.find('input[name="klarna_paymentmethod"]').removeAttr("disabled");
                n.find(".klarna_box_bottom:hidden").css("opacity", 0).show();
                n.find('input[type="text"]').width("auto");
                n.find("input:text:visible").each(function() {
                    r = jq(this).parent("div").width();
                    if (r) {
                        jq(this).css("max-width", r - 13)
                    }
                });
                jq(".proopc-klarna-payment .klarna_box_bottom_input_combo").each(function() {
                    if (jq(this).children("div").length == 1) {
                        jq(this).children("div").width("100%")
                    }
                });
                i = n.find(".klarna_box_bottom_right .klarna_box_bottom_content .btn-additional-klarna");
                if (!i.length) {
                    n.find(".klarna_box_bottom_right .klarna_box_bottom_content").append('<button type="button" class="proopc-btn btn-additional-klarna" onclick="return ProOPC.saveAdditionalPaymentdata();" style="margin-top: 15px;">' + VPOPC.JText._("COM_VIRTUEMART_SAVE") + "</button>")
                }
                jq(e).parents(".proopc-klarna-payment").find("div.klarna_box_bottom_title:visible, div.klarna_box_bottom_title:visible").removeAttr("style");
                jq(e).parents(".proopc-klarna-payment").find("#box_klarna_consent_invoice:visible").parent("div").addClass("proopc-klarna-consent-container").width("auto");
                jq(e).parents(".proopc-klarna-payment").find("#box_klarna_consent_part:visible").parent("div").addClass("proopc-klarna-consent-container").width("auto");
                jq(e).parents(".proopc-klarna-payment").find(".klarna_box_bottom:visible").animate({
                    opacity: 1
                }, 300)
            }
        },
        getKlarnaForm: function() {
            var e = jq("#proopc-payment-form").find("input:radio[name=virtuemart_paymentmethod_id]:checked");
            if (!e.hasClass("klarnaPayment")) {
                return
            }
            var t = e.parents("table");
            var n = t.find("*").serializeArray();
            n.push({
                name: "task",
                value: "setpayment"
            });
            n.push({
                name: "view",
                value: "cart"
            });
            n.push({
                name: "klarna_paymentmethod",
                value: e.next("input").val()
            });
            var r = jq("<form></form>");
            jq.each(n, function(e, t) {
                var n = jq("<input></input>");
                n.attr("type", "hidden");
                n.attr("name", t.name);
                n.attr("value", t.value);
                r.append(n)
            });
            return r
        },
        loadPaymentScripts: function() {
            ProOPC.addloader("#proopc-payments");
            jq.ajax({
                dataType: "json",
                url: VPOPC.option.URI,
                data: "ctask=getpaymentscripts",
                cache: false,
                success: function(e) {
                    jq("#proopc-payments").html(e.payments);
                    if (e.payment_scripts[0] !== "") {
                        payment_scripts = e.payment_scripts
                    }
                    if (e.payment_script[0] !== "") {
                        jq.each(e.payment_script, function(e, t) {
                            jq.getScript(t, function(e, t, n) {
                                if (typeof payment_scripts !== "undefined") {
                                    jq.each(payment_scripts, function(e, t) {
                                        t
                                    })
                                }
                            })
                        })
                    }
                },
                complete: function() {
                    ProOPC.style();
                    ProOPC.tooltip();
                    ProOPC.removeloader("#proopc-payments");
                    ProOPC.defaultSP()
                },
                error: function() {
                    console.log("Error: Error gettings Payment Scripts.")
                }
            })
        },
        loadShipmentScripts: function() {
            if (jq("#proopc-shipment-form").find("select").length <= 0) {
                return
            }
            ProOPC.addloader("#proopc-shipments");
            jq.ajax({
                dataType: "json",
                url: VPOPC.option.URI,
                data: "ctask=getshipmentscripts",
                cache: false,
                success: function(e) {
                    jq("#proopc-shipments").html(e.shipments);
                    if (e.shipment_scripts.length > 0 && e.shipment_scripts[0] !== "") {
                        jq.each(e.shipment_scripts, function(e, t) {
                            jq("head, body").find('script[type="text/javascript"]').each(function() {
                                var e = jq(this).attr("src");
                                if (typeof e === "undefined" || e === false) {
                                    jq(this).append(t);
                                    return false
                                }
                            })
                        })
                    }
                },
                complete: function() {
                    ProOPC.style();
                    ProOPC.tooltip();
                    ProOPC.removeloader("#proopc-shipments");
                    ProOPC.defaultSP()
                },
                error: function() {
                    console.log("Error: Error gettings Shipment Scripts.")
                }
            })
        },
        getUrlVars: function() {
            var e = {},
                t, n;
            var r = ["option", "view", "task", "ctask"];
            var i = window.location.href.slice(window.location.href.indexOf("?") + 1).split("&");
            for (var s = 0; s < i.length; s++) {
                t = i[s].split("=");
                if (jq.inArray(t[0], r) == -1 && typeof t[1] !== "undefined" && t[1] != null) {
                    e[t[0]] = t[1]
                }
            }
            return jq.param(e)
        },
        canCheckout: function() {
            if (jq("#ProOPC").length && !jq("#ProOPC").hasClass("emptyCart-view")) {
                ProOPC._canCheckoutTask();
                setInterval(function() {
                    ProOPC._canCheckoutTask()
                }, 9e5)
            }
        },
        _canCheckoutTask: function() {
            jq.ajax({
                dataType: "json",
                url: VPOPC.option.URI,
                data: "ctask=cancheckout",
                cache: false,
                success: function(e) {
                    if (e.error == 1) {
                        jq("#ProOPC").removeClass("canCheckout");
                        if (e.reload == 1) window.location.reload(true)
                    } else if (e.error == 0) {
                        jq("#ProOPC").addClass("canCheckout")
                    }
                },
                error: function(e, t, n) {
                    console.log("Error: ProOPC._canCheckoutTask.");
                    console.log(e);
                    console.log(t);
                    console.log(n)
                }
            })
        },
        resetForm: function(e, t) {
            jq(t, e).val("");
            jq(e).submit()
        }
    };
    jq(document).ready(function() {
        if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
            var msViewportStyle = document.createElement("style");
            msViewportStyle.appendChild(
                document.createTextNode(
                    "@-ms-viewport{width:auto!important}"
                ));
            document.getElementsByTagName("head")[0].appendChild(msViewportStyle);
        }
        ProOPC.canCheckout();
        ProOPC.spinnervars();
        ProOPC.verifyRegForm();
        ProOPC.tooltip();
        ProOPC.fieldTip();
        ProOPC.inputwidth();
        ProOPC.selectwidth();
        ProOPC.defaultSP();
        ProOPC.productdetails();
    });
    jq(document).ajaxStop(function() {
        ProOPC.productdetails()
    });
    jq(window).load(function() {
        ProOPC.style()
    });
    jq(window).resize(function() {
        var e = navigator.userAgent.toLowerCase();
        var t = e.indexOf("android") > -1;
        if (!t) {
            ProOPC.style();
            ProOPC.inputwidth();
            ProOPC.selectwidth()
        }
    })
}