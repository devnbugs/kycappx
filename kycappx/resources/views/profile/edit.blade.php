<x-layouts.dashboard-user title="Profile" header="Profile & Security">
    <div class="grid gap-6 xl:grid-cols-2">
        <div class="surface-card p-6 sm:p-8 xl:col-span-2">
            <div class="max-w-2xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="surface-card p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="surface-card p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-layouts.dashboard-user>
