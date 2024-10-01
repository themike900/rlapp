<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Danke für die Registrierung! Bevor es losgehen kann, bestätige bitte deine Eamil-Adresse mit einem Klick auf den Link in der Email die ich dir gerade gesendet habe.? Falls du die Eamil nicht erhalten hast, schicke ich sie dir gerne noch einmal.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('Ich habe dir einen neuen Bestätigungslink an die registrierte Email-Adresse gesendet.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Bestätigungs-Email erneut versenden') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('Abmelden') }}
            </button>
        </form>
    </div>
</x-guest-layout>
