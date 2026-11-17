<div x-data="{ confirmLogout: false }" @keydown.escape.window="confirmLogout = false">
    <flux:dropdown position="bottom" align="start">
        <flux:sidebar.profile
            :name="auth()->user()->name"
            :initials="auth()->user()->initials()"
            icon:trailing="chevrons-up-down"
            data-test="sidebar-menu-button"
        />

        <flux:menu>
            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                <flux:avatar
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                />
                <div class="grid flex-1 text-start text-sm leading-tight">
                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                </div>
            </div>
            <flux:menu.separator />
            <flux:menu.radio.group>
                <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                    Profil &amp; Pengaturan
                </flux:menu.item>
                <flux:menu.item
                    as="button"
                    type="button"
                    icon="arrow-right-start-on-rectangle"
                    class="w-full cursor-pointer text-rose-600 dark:text-rose-400"
                    @click="confirmLogout = true"
                    data-test="open-logout-modal-button"
                >
                    Keluar
                </flux:menu.item>
            </flux:menu.radio.group>
        </flux:menu>
    </flux:dropdown>

    <x-logout-confirmation-modal title-id="sidebar-logout-modal-title" />
</div>
