/**
 * Admin layout - sidebar toggle & collapse
 */
(function () {
    "use strict";

    const sidebar = document.getElementById("adminSidebar");
    const mobileToggle = document.getElementById("sidebarToggle");
    const collapseToggle = document.getElementById("sidebarCollapseToggle");
    const storageKey = "ims_admin_sidebar_collapsed";

    // Restore collapsed state (desktop)
    try {
        const collapsed = localStorage.getItem(storageKey) === "1";
        if (collapsed) {
            document.body.classList.add("sidebar-collapsed");
        }
    } catch (e) {}

    // Mobile show/hide (offcanvas-like)
    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener("click", function () {
            sidebar.classList.toggle("show");
        });
    }

    // Desktop collapse/expand
    if (collapseToggle) {
        collapseToggle.addEventListener("click", function () {
            document.body.classList.toggle("sidebar-collapsed");
            try {
                localStorage.setItem(
                    storageKey,
                    document.body.classList.contains("sidebar-collapsed")
                        ? "1"
                        : "0",
                );
            } catch (e) {}
        });
    }

    //log out

    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", async function (e) {
            e.preventDefault();
            await doLogout();
        });
    }

    async function doLogout() {
        const token = localStorage.getItem("token");
        const url = "/api/v1/logout";

        try {
            if (token) {
                const response = await axios.post(
                    url,
                    {},
                    {
                        headers: { Authorization: "Bearer " + token },
                    },
                );
                if (response.status !== 200) {
                    showErrorToast("Logout failed. Please try again.");
                }
            }
        } catch (err) {
            showErrorToast(
                getErrorMessage(err, "Logout failed. Please try again."),
            );
        } finally {
            localStorage.removeItem("token");
            localStorage.removeItem("user");
            document.cookie = "api_token=; path=/; max-age=0; SameSite=Lax";
            window.location.href = "/login";
        }
    }
})();
