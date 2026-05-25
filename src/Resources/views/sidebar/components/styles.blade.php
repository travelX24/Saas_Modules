<style>
    :root {
        --brand-via: #ec4899; /* fallback */
        --brand-via-rgb: 236 72 153; /* fallback */

        /* ✅ حتى language-switcher يشتغل لو ماعندك brand-from/to */
        --brand-from: #f97316;
        --brand-to: #ec4899;

        --sidebar-width: 260px;
        --sidebar-collapsed: 80px;
        --transition-speed: 0.3s;

        --light-bg: #f8f9fa;
        --dark-text: #2d3748;
        --gray-text: #718096;

        --border-color: rgba(255, 255, 255, 0.12);
        --hover-color: rgba(255, 255, 255, 0.10);

        /* ✅ ظل حسب الاتجاه */
        --sidebar-shadow-x: 5px; /* LTR (sidebar left) shadow to right */
    }

    [dir="rtl"] {
        --sidebar-shadow-x: -5px; /* RTL (sidebar right) shadow to left */
    }

    /* shell */
    .saas-shell {
        display: flex;
        min-height: 100vh;
        background: var(--light-bg);
        color: var(--dark-text);
    }

    /* Sidebar (✅ على inline-start: يسار في LTR + يمين في RTL) */
    .sidebar {
        position: fixed;
        inset-block-start: 0;
        inset-inline-start: 0;
        height: 100vh;
        width: var(--sidebar-width);

        /* ✅ لون واحد شفاف */
        background-color: rgba(117, 67, 235, 0.95) !important; /* fallback */
        background-color: rgb(var(--brand-via-rgb) / 0.95) !important;

        backdrop-filter: blur(10px);

        color: #fff;
        box-shadow: var(--sidebar-shadow-x) 0 15px rgba(0, 0, 0, 0.10);
        z-index: 1000;
        display: flex;
        flex-direction: column;

        /* ✅ Animation base */
        transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.4s ease;
    }

    .sidebar.collapsed { width: var(--sidebar-collapsed); }

    /* Toggle (✅ دائماً على الحافة الخارجية للـsidebar) */
    .toggle-btn {
        position: absolute;
        inset-inline-end: -12px;
        inset-block-start: 20px;

        background-color: var(--brand-via);
        color: #fff;
        border: 2px solid #fff;
        border-radius: 50%;
        width: 24px;
        height: 24px;

        display: flex;
        align-items: center;
        justify-content: center;

        cursor: pointer;
        font-size: 12px;
        transition: transform var(--transition-speed);
        z-index: 1001;
    }

    .sidebar.collapsed .toggle-btn { transform: rotate(180deg); }

    /* Header */
    .sidebar-header {
        padding: 25px 20px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid var(--border-color);
        transition: padding var(--transition-speed);
    }

    .sidebar.collapsed .sidebar-header {
        padding: 25px 15px;
        justify-content: center;
    }

    .app-logo {
        width: 40px;
        height: 40px;
        background-color: #fff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--brand-via);
        font-weight: bold;
        font-size: 20px;
        margin-inline-end: 15px;
    }

    .sidebar.collapsed .app-logo { margin-inline-end: 0; }

    .app-info { display: flex; flex-direction: column; }
    .sidebar.collapsed .app-info { display: none; }

    .app-name { font-weight: 700; font-size: 18px; }

    /* Profile */
    .profile-section {
        padding: 25px 20px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid var(--border-color);
        transition: padding var(--transition-speed);
    }

    .sidebar.collapsed .profile-section {
        padding: 25px 15px;
        justify-content: center;
    }

    .profile-img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: 3px solid rgba(255, 255, 255, 0.30);
        object-fit: cover;
        margin-inline-end: 15px;

        background-color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;

        color: var(--brand-via);
        font-size: 20px;
        overflow: hidden;
    }

    .sidebar.collapsed .profile-img {
        width: 40px;
        height: 40px;
        margin-inline-end: 0;
    }

    .profile-info { display: flex; flex-direction: column; flex-grow: 1; }
    .sidebar.collapsed .profile-info { display: none; }

    .profile-name { font-weight: 600; font-size: 16px; margin-bottom: 3px; }
    .profile-role {
        font-size: 13px;
        opacity: 0.95;
        background-color: rgba(255, 255, 255, 0.18);
        padding: 2px 8px;
        border-radius: 10px;
        width: fit-content;
    }

    /* Nav */
    .nav-links {
        flex-grow: 1;
        padding: 20px 0;
        overflow-y: auto;
    }

    .sidebar.collapsed .nav-links {
        overflow-y: visible;
    }

    .nav-item { 
        margin-bottom: 5px; 
        transition: transform 0.4s ease, opacity 0.4s ease;
    }

    @media (max-width: 768px) {
        .nav-item {
            transform: scale(0.8) translateY(10px);
            opacity: 0;
        }
        
        .sidebar.open .nav-item {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        .sidebar.open .nav-item:nth-child(1) { transition-delay: 0.1s; }
        .sidebar.open .nav-item:nth-child(2) { transition-delay: 0.15s; }
        .sidebar.open .nav-item:nth-child(3) { transition-delay: 0.2s; }
        .sidebar.open .nav-item:nth-child(4) { transition-delay: 0.25s; }
        .sidebar.open .nav-item:nth-child(5) { transition-delay: 0.3s; }
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 15px 20px;

        color: rgba(255, 255, 255, 0.92);
        text-decoration: none;
        transition: all var(--transition-speed);
        position: relative;
    }

    .sidebar.collapsed .nav-link {
        padding: 15px 25px;
        justify-content: center;
    }

    .nav-link:hover { background-color: var(--hover-color); color: #fff; }

    .nav-link.active {
        color: #fff;
        background-color: rgba(255, 255, 255, 0.12);
    }

    /* Active underline */
    .nav-link.active::after {
        content: '';
        position: absolute;
        inset-inline-start: 18px;
        inset-inline-end: 18px;
        inset-block-end: 8px;

        height: 3px;
        background-color: rgba(255,255,255,.92);
        border-radius: 999px;
        box-shadow: 0 0 14px rgba(255,255,255,.35);
    }

    .sidebar.collapsed .nav-link.active::after { display: none; }

    .nav-icon {
        font-size: 20px;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-inline-end: 15px;
    }

    .sidebar.collapsed .nav-icon { margin-inline-end: 0; }

    .nav-text { font-weight: 500; font-size: 15px; }
    .sidebar.collapsed .nav-text { display: none; }

    /* Options */
    .sidebar-options {
        padding: 20px;
        border-top: 1px solid var(--border-color);
    }
    .sidebar.collapsed .sidebar-options { padding: 20px 15px; }

    .language-switcher { margin-bottom: 20px; }
/* ✅ Show/Hide by sidebar state */
    .sidebar .collapsed-only { display: none; }
    .sidebar.collapsed .collapsed-only {
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: center;
    }

    .sidebar.collapsed .expanded-only { display: none; }

    /* ✅ Mini language buttons (collapsed) */
    .lang-mini{
        width: 44px;
        height: 44px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;

        background-color: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.22);

        color: #fff;
        text-decoration: none;
        font-weight: 800;
        font-size: 12px;
        letter-spacing: .5px;

        transition: all .2s ease;
    }

    .lang-mini:hover{
        background-color: rgba(255, 255, 255, 0.22);
    }

    .lang-mini.active{
        background-color: #fff;
        color: var(--brand-via);
    }
/* ✅ Center language block nicely */
.lang-title-center{
    width: 100%;
    justify-content: center;
    text-align: center;
}

.lang-center{
    display: flex;
    justify-content: center;
    width: 100%;
}

.lang-center-col{
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

/* mini title icon (collapsed) */
.lang-title-mini{
    width: 34px;
    height: 34px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.18);
    color: #fff;
    margin-bottom: 2px;
}

    .language-switcher-title {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.85);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .sidebar.collapsed .language-switcher-title { display: none; }

    /* Logout */
    .logout-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;

        background-color: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.30);
        color: #fff;

        border-radius: 10px;
        padding: 12px 20px;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        width: 100%;
    }

    .sidebar.collapsed .logout-btn {
        padding: 12px;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        margin: 0 auto;
    }

    .logout-btn:hover {
        background-color: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
    }

    .sidebar.collapsed .logout-btn-text { display: none; }

    /* ✅ Main content margin (inline-start = يمين في RTL + يسار في LTR) */
    .main-content {
        flex-grow: 1;
        padding: 15px;
        margin-inline-start: var(--sidebar-width);
        transition: margin-inline-start var(--transition-speed);
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    .sidebar.collapsed ~ .main-content {
        margin-inline-start: var(--sidebar-collapsed);
    }

    @media (min-width: 640px) {
        .main-content {
            padding: 20px;
        }
    }

    @media (min-width: 1024px) {
        .main-content {
            padding: 30px;
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        /* اخفاء حسب الاتجاه */
        .sidebar {
            inset-block: 20px; /* ✅ Floating from top and bottom */
            inset-inline: 20px; /* ✅ Floating from sides */
            height: calc(100vh - 40px);
            width: 280px;
            max-width: calc(100vw - 40px);
            border-radius: 24px; /* ✅ Rounded corners like a card */
            transform: scale(0);
            opacity: 0;
            transform-origin: top left; /* LTR: top-left */
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        [dir="rtl"] .sidebar {
            transform: scale(0);
            transform-origin: top right; /* RTL: top-right */
        }

        .sidebar.open { 
            transform: scale(1);
            opacity: 1;
        }
        .sidebar.collapsed { width: var(--sidebar-collapsed); }

        .main-content {
            margin-inline-start: 0 !important;
            width: 100%;
            padding: 15px;
        }

        .mobile-toggle{
            display: block;
            position: fixed;
            inset-block-start: 20px;
            inset-inline-start: 20px; /* ✅ Even more away for better look */
            background-color: var(--brand-via);
            color: #fff;
            border: 3px solid #fff; /* ✅ White border as requested */
            border-radius: 14px;
            width: 46px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1002; /* Even higher to stay on top of everything */
            font-size: 18px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .mobile-toggle:active {
            transform: scale(0.9);
        }

        /* ✅ إخفاء زر السهم على الشاشات الصغيرة */
        .toggle-btn {
            display: none;
        }

        /* تحسين Sidebar على الموبايل */
        .sidebar-header {
            padding: 35px 15px 20px 80px !important; /* ✅ Even more space to clear the button */
        }
        [dir="rtl"] .sidebar-header {
            padding: 35px 80px 20px 15px !important; 
        }

        .profile-section {
            padding: 20px 15px;
        }

        .nav-link {
            padding: 12px 15px;
            font-size: 14px;
        }

        .sidebar-options {
            padding: 15px;
        }

        /* Backdrop when sidebar is open */
        .sidebar-backdrop {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 998;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease, visibility 0.4s ease;
        }

        .sidebar.open ~ .sidebar-backdrop {
            opacity: 1;
            visibility: visible;
        }

        .mobile-header-adjust {
            padding-inline-start: 90px !important;
            padding-top: 10px !important;
        }
    }

    @media (min-width: 769px) {
        .mobile-toggle { display: none; }
    }

    @media (min-width: 769px) and (max-width: 1024px) {
        .main-content {
            padding: 20px;
        }
    }
</style>
