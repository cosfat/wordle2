<div>
        <x-guest-layout>
            <x-jet-authentication-card>
                <x-slot name="logo">
                    <x-jet-authentication-card-logo/>
                </x-slot>

                <x-jet-validation-errors class="mb-4"/>

                @if (session('status'))
                    <div class="mb-4 font-medium text-sm text-green-600">
                        {{ session('status') }}
                    </div>
                @endif
                <div class="flex justify-center">
                <h2 class="text-2xl font-bold tracking-tight sm:text-center sm:text-4xl text-indigo-500">
                    Yemi Adresimiz kelimeo.com</h2></div>

                <div class="flex justify-center"><h2>Lütfen mevcut şifrenizle giriş yapın</h2></div>
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div>
                        <x-jet-label for="username" value="{{ __('Kullanıcı adı') }}"/>
                        <x-jet-input id="username" class="block mt-1 w-full" type="text" name="username"
                                     :value="old('username')"
                                     required autofocus/>
                    </div>

                    <div class="mt-4">
                        <x-jet-label for="password" value="{{ __('Password') }}"/>
                        <x-jet-input id="password" class="block mt-1 w-full" type="password" name="password" required
                                     autocomplete="current-password"/>
                    </div>

                    <div class="block mt-4">
                        <label for="remember_me" class="flex items-center">
                            <x-jet-checkbox id="remember_me" name="remember"/>
                            <span class="ml-2 text-sm text-gray-600">{{ __('Beni hatırla') }}</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        @if (Route::has('password.request'))
                            <a class="underline text-sm text-gray-600 hover:text-gray-900"
                               href="{{ route('password.request') }}">
                                {{ __('Parola hatırlatıcısı') }}
                            </a>
                        @endif
                        <x-jet-button class="ml-4">
                            {{ __('Giriş') }}
                        </x-jet-button>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('register') }}">
                            {{ __('Üye ol') }}
                        </a>
                    </div>
                </form>
            </x-jet-authentication-card>
        </x-guest-layout>

    @if (session()->has('message'))
        <script>
            notifyGame("{{  session('message')  }}")
        </script>
    @endif
    </div>
