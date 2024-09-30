<div>
    <div class="min-h-screen bg-gray-100">
<!-- Navbar -->
        <div class="drawer">
            <input id="my-drawer" type="checkbox" class="drawer-toggle" />
                <div class="drawer-content">
                <!-- Page content here -->
                    <label for="my-drawer" class="btn btn-square btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-5 h-5 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </label>
                </div> 
        </div>
        <div class="navbar bg-base-100">
            <div class="flex-none">
                    
            </div>
            <div class="flex-1">
                <a class="btn btn-ghost text-xl">daisyUI</a>
            </div>
            <div class="flex-none">
                <button class="btn btn-square btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-5 h-5 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
                </button>
            </div>
            <div class="flex justify-end gap-2">
                <div class="form-control">
                    <h3>{{ $page.props.auth.user.name }}</h3>
                </div>
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                        <div class="w-10 rounded-full">
                            <img alt="Profile" src="https://img.daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg" />
                        </div>
                    </div>
                    <ul tabindex="0" class="mt-3 z-[1] p-2 shadow menu menu-sm dropdown-content bg-base-100 rounded-box w-52">
                        <li>
                            <a class="justify-between">
                                Profile
                                <span class="badge">New</span>
                            </a>
                        </li>
                        <li><a :href="route('profile.edit')">Settings</a></li>
                        <li><Link :href="route('logout')" method="post" as="button">Logout</Link></li>
                    </ul>
                </div>
            </div>
            <div class="drawer-side">
                <label for="my-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
                <ul class="menu menu-lg p-4 w-80 min-h-full bg-base-200 text-base-content">
                <!-- Sidebar content here -->
                    <a class="btn btn-ghost text-lg">
                        Company Name
                    </a>
                    <h2 class="font-bold text-lg">{{ $page.props.auth.user.name }}</h2>
                    <span class="text-sm text-accent">username@email.com</span>
                    <li>
                        <a>
                            <i class="fa-solid fa-inbox fa-fw"></i>
                            Inbox
                        </a>
                    </li>
                    <li>
                        <a class="active">
                            <i class="fa-solid fa-user fa-fw"></i>
                            Accounts
                        </a>
                    </li>
                    <li>
                        <a>
                            <i class="fa-solid fa-ticket fa-fw"></i>
                            Tickets
                        </a>
                    </li>
                    <li>
                        <a>
                            <i class="fa-solid fa-gear fa-fw"></i>
                            Settings
                        </a>
                    </li>
                
                </ul>
            </div>
        </div>
                <a class="btn btn-ghost text-lg">
                    <img alt="Logo" src="/logo.svg" class="w-4" />
                    Company 
                </a>
        </div>

<!-- Page Layout -->
        <div class="flex">
            <!-- Main Content Area -->
            <div class="flex-grow">
                <!-- Page Heading -->
                <header class="bg-white shadow" v-if="$slots.header">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
                <main class="p-4">
                    <slot />
                </main>
            </div>
        </div>
</div>