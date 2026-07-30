<?php
include "../inc/db_cfg.php";
?>

<!doctype html>
<html lang="en">
<!--begin::Head-->

<head>
    <script>
    (function(w, i, g) {
        w[g] = w[g] || [];
        if (typeof w[g].push == 'function') w[g].push(i)
    })
    (window, 'GTM-WHH7CJ83', 'google_tags_first_party');
    </script>
    <script>
    (function(w, d, s, l) {
        w[l] = w[l] || [];
        (function() {
            w[l].push(arguments);
        })('set', 'developer_id.dYzg1YT', true);
        w[l].push({
            'gtm.start': new Date().getTime(),
            event: 'gtm.js'
        });
        var f = d.getElementsByTagName(s)[0],
            j = d.createElement(s);
        j.async = true;
        j.src = '/wzrt/';
        f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer');
    </script>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <title>Admin</title>

    <!--begin::Theme Init (prevents flash of incorrect theme on load, #6043)-->
    <script>
    (() => {
        'use strict';
        const STORAGE_KEY = 'lte-theme';
        let stored = null;
        try {
            stored = localStorage.getItem(STORAGE_KEY);
        } catch {
            // localStorage may be unavailable (private mode, sandboxed iframe).
        }
        const prefersDark = globalThis.matchMedia('(prefers-color-scheme: dark)').matches;
        // Mirror the resolution in _scripts.astro: explicit "dark"/"light" win,
        // otherwise ("auto" or unset) fall back to the OS preference.
        let resolved = 'light';
        if (stored === 'dark' || stored === 'light') {
            resolved = stored;
        } else if (prefersDark) {
            resolved = 'dark';
        }
        document.documentElement.setAttribute('data-bs-theme', resolved);
        document.documentElement.style.colorScheme = resolved;
    })();
    </script>
    <!--end::Theme Init-->

    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->

    <!--begin::Primary Meta Tags-->
    <meta name="title" content="Admin" />
    <meta name="author" content="ColorlibHQ" />
    <meta name="description"
        content="AdminLTE is a free Bootstrap 5 admin dashboard template with almost 50 example pages, built with vanilla JS and designed with accessibility in mind." />
    <meta name="keywords"
        content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel" />
    <!--end::Primary Meta Tags-->

    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />

    <link rel="icon" href="<?= BASE_URL ?>assets/img/favicon.png" type="image/x-icon">

    <link rel="preload" href="<?= BASE_URL ?>assets/css/adminlte.css" as="style" />

    <!--end::Accessibility Features-->
    <!--begin::Fonts-->

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/index.css"
        integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" media="print"
        onload="this.media = 'all'" />
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/overlayscrollbars.min.css" crossorigin="anonymous" />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/bootstrap-icons.min.css" crossorigin="anonymous" />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/adminlte1.css" />
    <!--end::Required Plugin(AdminLTE)-->

    <!-- apexcharts -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/apexcharts.css"
        integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0=" crossorigin="anonymous" />
    <!-- jsvectormap -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/jsvectormap.min.css"
        integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4=" crossorigin="anonymous" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/tabulator_bootstrap5.min.css" crossorigin="anonymous" />

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/own.css?v=20260717" crossorigin="anonymous" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
    .small-popup {
        padding: 0.75rem !important;
        border-radius: 0.9rem !important;
    }

    .small-title {
        font-size: 1rem !important;
        line-height: 1.2 !important;
        margin: 0 !important;
    }
    </style>




    <script src="<?= BASE_URL ?>assets/js/tabulator.min.js" crossorigin="anonymous"></script>
    <script src="<?= BASE_URL ?>assets/js/sweetalert_helper.js?v=20260720" crossorigin="anonymous"></script>


    <script data-cfasync="false" nonce="85f02340-5054-4be7-9481-aeec8366e64b">
    try {
        (function(w, d) {
            ! function(mw, mx, my, mz) {
                if (mw.zaraz) console.error("zaraz is loaded twice");
                else {
                    mw[my] = mw[my] || {};
                    mw[my].executed = [];
                    mw.zaraz = {
                        deferred: [],
                        listeners: []
                    };
                    mw.zaraz._v = "5887";
                    mw.zaraz._n = "85f02340-5054-4be7-9481-aeec8366e64b";
                    mw.zaraz.q = [];
                    mw.zaraz._f = function(mA) {
                        return async function() {
                            var mB = Array.prototype.slice.call(arguments);
                            mw.zaraz.q.push({
                                m: mA,
                                a: mB
                            })
                        }
                    };
                    for (const mC of ["track", "set", "debug"]) mw.zaraz[mC] = mw.zaraz._f(mC);
                    mw.zaraz.init = () => {
                        var mD = mx.getElementsByTagName(mz)[0],
                            mE = mx.createElement(mz),
                            mF = mx.getElementsByTagName("title")[0];
                        mF && (mw[my].t = mx.getElementsByTagName("title")[0].text);
                        mw[my].x = Math.random();
                        mw[my].w = mw.screen.width;
                        mw[my].h = mw.screen.height;
                        mw[my].j = mw.innerHeight;
                        mw[my].e = mw.innerWidth;
                        mw[my].l = mw.location.href;
                        mw[my].r = mx.referrer;
                        mw[my].k = mw.screen.colorDepth;
                        mw[my].n = mx.characterSet;
                        mw[my].o = (new Date).getTimezoneOffset();
                        if (mw.dataLayer)
                            for (const mG of Object.entries(Object.entries(dataLayer).reduce((mH, mI) => ({
                                    ...mH[1],
                                    ...mI[1]
                                }), {}))) zaraz.set(mG[0], mG[1], {
                                scope: "page"
                            });
                        mw[my].q = [];
                        for (; mw.zaraz.q.length;) {
                            const mJ = mw.zaraz.q.shift();
                            mw[my].q.push(mJ)
                        }
                        mE.defer = !0;
                        for (const mK of [localStorage, sessionStorage]) Object.keys(mK || {}).filter(mM => mM
                            .startsWith("_zaraz_")).forEach(mL => {
                            try {
                                mw[my]["z_" + mL.slice(7)] = JSON.parse(mK.getItem(mL))
                            } catch {
                                mw[my]["z_" + mL.slice(7)] = mK.getItem(mL)
                            }
                        });
                        mE.referrerPolicy = "origin";
                        mE.src = "/cdn-cgi/zaraz/s.js?z=" + btoa(encodeURIComponent(JSON.stringify(mw[my])));
                        mD.parentNode.insertBefore(mE, mD)
                    };
                    ["complete", "interactive"].includes(mx.readyState) ? zaraz.init() : mw.addEventListener(
                        "DOMContentLoaded", zaraz.init)
                }
            }(w, d, "zarazData", "script");
            window.zaraz._p = async nn => new Promise(no => {
                if (nn) {
                    nn.e && nn.e.forEach(np => {
                        try {
                            const nq = d.querySelector("script[nonce]"),
                                nr = nq?.nonce || nq?.getAttribute("nonce"),
                                ns = d.createElement("script");
                            nr && (ns.nonce = nr);
                            ns.innerHTML = np;
                            ns.onload = () => {
                                d.head.removeChild(ns)
                            };
                            d.head.appendChild(ns)
                        } catch (nt) {
                            console.error(`Error executing script: ${np}\n`, nt)
                        }
                    });
                    Promise.allSettled((nn.f || []).map(nu => fetch(nu[0], nu[1])))
                }
                no()
            });
            zaraz._p({
                "e": ["(function(w,d){})(window,document)"]
            });
        })(window, document)
    } catch (e) {
        throw fetch("/cdn-cgi/zaraz/t"), e;
    };
    </script>

</head>
<!--end::Head-->