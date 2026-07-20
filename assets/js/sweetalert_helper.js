(function () {
    window.appAlert = function(message, title = "Notice") {
        return Swal.fire({
            position: "center",
            icon: "info",
            title,
            text: String(message ?? ""),
            showConfirmButton: true,
            width: "300px",
            customClass: {
                popup: "small-popup",
                title: "small-title"
            }
        });
    };

    window.appError = function(message, title = "Error") {
        return Swal.fire({
            position: "center",
            icon: "error",
            title: String(message ?? title),
            showConfirmButton: true,
            width: "300px",
            customClass: {
                popup: "small-popup",
                title: "small-title"
            }
        });
    };

    window.appSuccess = function(message = "Saved Successfully", timer = 1500, html = "") {
        return Swal.fire({
            position: "center",
            icon: "success",
            title: message,
            html,
            showConfirmButton: false,
            timer,
            width: html ? "320px" : "260px",
            customClass: {
                popup: "small-popup",
                title: "small-title"
            }
        });
    };

    window.appConfirm = function(message, confirmButtonText = "Yes") {
        return Swal.fire({
            icon: "question",
            title: message,
            showCancelButton: true,
            confirmButtonText,
            cancelButtonText: "Cancel",
            width: "320px",
            customClass: {
                popup: "small-popup",
                title: "small-title"
            }
        });
    };

    window.__lastSwalToast = null;
    window.__markSwalToast = function(action, ttl = 1600) {
        window.__lastSwalToast = {
            action: String(action || "").toLowerCase(),
            expiresAt: Date.now() + ttl
        };

        setTimeout(() => {
            if (window.__lastSwalToast && window.__lastSwalToast.expiresAt <= Date.now()) {
                window.__lastSwalToast = null;
            }
        }, ttl + 25);
    };

    window.__shouldSuppressAlert = function(text) {
        const toast = window.__lastSwalToast;
        if (!toast || toast.expiresAt < Date.now()) {
            return false;
        }

        const lower = String(text ?? "").trim().toLowerCase();
        if (!lower) {
            return false;
        }

        if (toast.action === "save") {
            return lower === "success" || lower.includes("saved successfully") || lower.includes("updated successfully");
        }

        if (toast.action === "update") {
            return lower === "success" || lower.includes("updated successfully") || lower.includes("saved successfully");
        }

        if (toast.action === "delete") {
            return lower === "success" || lower.includes("deleted successfully");
        }

        if (toast.action === "restore") {
            return lower === "success" || lower.includes("restored successfully");
        }

        return false;
    };

    window.__formatMasterLabel = function(masterName) {
        return String(masterName || "")
            .replace(/_/g, " ")
            .replace(/\b\w/g, function(ch) {
                return ch.toUpperCase();
            })
            .trim();
    };

    window.__formatMasterSuccess = function(masterName, action) {
        const label = window.__formatMasterLabel(masterName);
        const actionLabel = String(action || "").toLowerCase();

        if (!label) {
            if (actionLabel === "delete") return "Deleted Successfully";
            if (actionLabel === "restore") return "Restored Successfully";
            if (actionLabel === "update") return "Updated Successfully";
            return "Saved Successfully";
        }

        if (actionLabel === "delete") return `${label} Deleted Successfully`;
        if (actionLabel === "restore") return `${label} Restored Successfully`;
        if (actionLabel === "update") return `${label} Updated Successfully`;
        return `${label} Saved Successfully`;
    };

    window.alert = function(message) {
        const text = String(message ?? "").trim();
        const lower = text.toLowerCase();

        if (window.__shouldSuppressAlert(text)) {
            return;
        }

        if (lower === "success" || lower === "saved successfully" || lower === "saved successfully.") {
            return window.appSuccess("Saved Successfully");
        }

        if (lower.includes("updated successfully")) {
            return window.appSuccess("Updated Successfully");
        }

        if (lower.includes("restored successfully")) {
            return window.appSuccess("Restored Successfully");
        }

        if (lower.includes("deleted successfully")) {
            return window.appSuccess("Deleted Successfully");
        }

        if (lower.includes("failed") || lower.includes("error")) {
            return window.appError(text || "Error");
        }

        return window.appAlert(message);
    };

    window.__nativeConfirm = window.confirm.bind(window);
    window.__swalConfirmToken = false;
    window.confirm = function(message) {
        if (window.__swalConfirmToken) {
            return true;
        }
        return window.__nativeConfirm(message);
    };

    document.addEventListener("click", function(e) {
        const btn = e.target.closest(".delete-btn, .recycle-btn");
        if (!btn || btn.dataset.swalHandled === "1") {
            return;
        }

        const actionText = btn.classList.contains("recycle-btn") ? "restore" : "delete";

        e.preventDefault();
        e.stopImmediatePropagation();

        Swal.fire({
            icon: "question",
            title: `Are you sure you want to ${actionText} this record?`,
            showCancelButton: true,
            confirmButtonText: `Yes, ${actionText}`,
            cancelButtonText: "Cancel",
            width: "320px",
            customClass: {
                popup: "small-popup",
                title: "small-title"
            }
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            btn.dataset.swalHandled = "1";
            window.__swalConfirmToken = true;
            btn.click();
            window.__swalConfirmToken = false;
            setTimeout(() => {
                delete btn.dataset.swalHandled;
            }, 0);
        });
    }, true);

    const originalFetch = window.fetch.bind(window);
    window.fetch = async function(...args) {
        const input = args[0];
        const init = args[1] || {};
        const method = String(init.method || (typeof input === "object" && input && input.method) || "GET").toUpperCase();
        const url = typeof input === "string" ? input : (input && input.url) ? input.url : "";
        const response = await originalFetch(...args);

        try {
            const isMasterAction = /(?:^|\/)qur_[a-z_]+_master\.php/i.test(url);
            const actionMatch = url.match(/[?&]action=([a-z_]+)/i);
            const action = actionMatch ? actionMatch[1].toLowerCase() : "";
            const masterMatch = url.match(/(?:^|\/)qur_([a-z_]+)_master\.php/i);
            const masterName = masterMatch ? masterMatch[1].toLowerCase() : "";
            const getBodyValue = (name) => {
                const body = init.body;

                if (!body || !name) {
                    return "";
                }

                if (typeof FormData !== "undefined" && body instanceof FormData) {
                    return String(body.get(name) ?? "");
                }

                if (typeof URLSearchParams !== "undefined" && body instanceof URLSearchParams) {
                    return String(body.get(name) ?? "");
                }

                if (typeof body === "string") {
                    try {
                        return String(new URLSearchParams(body).get(name) ?? "");
                    } catch (error) {
                        return "";
                    }
                }

                return "";
            };
            const isUpdate = action === "save" && Boolean(getBodyValue("id").trim() || (masterName && getBodyValue(masterName + "_id").trim()));

            if (isMasterAction && method === "POST" && action === "delete") {
                const clone = response.clone();
                const contentType = clone.headers.get("content-type") || "";
                let ok = false;

                if (contentType.includes("application/json")) {
                    const payload = await clone.json();
                    ok = payload && payload.status === "success";
                } else {
                    ok = (await clone.text()).trim() === "success";
                }

                if (ok) {
                    window.appSuccess(window.__formatMasterSuccess(masterName, "delete"));
                    window.__markSwalToast("delete");
                }
            }

            if (isMasterAction && method === "POST" && action === "restore") {
                const clone = response.clone();
                const contentType = clone.headers.get("content-type") || "";
                let ok = false;

                if (contentType.includes("application/json")) {
                    const payload = await clone.json();
                    ok = payload && payload.status === "success";
                } else {
                    ok = (await clone.text()).trim() === "success";
                }

                if (ok) {
                    window.appSuccess(window.__formatMasterSuccess(masterName, "restore"));
                    window.__markSwalToast("restore");
                }
            }

            if (isMasterAction && method === "POST" && action === "save" && !/qur_employee_master\.php/i.test(url)) {
                const clone = response.clone();
                const contentType = clone.headers.get("content-type") || "";
                let ok = false;

                if (contentType.includes("application/json")) {
                    const payload = await clone.json();
                    ok = payload && payload.status === "success";
                } else {
                    ok = (await clone.text()).trim() === "success";
                }

                if (ok) {
                    window.appSuccess(window.__formatMasterSuccess(masterName, isUpdate ? "update" : "save"));
                    window.__markSwalToast(isUpdate ? "update" : "save");
                }
            }
        } catch (error) {
            // Ignore toast parsing errors and let the app keep working.
        }

        return response;
    };
})();
