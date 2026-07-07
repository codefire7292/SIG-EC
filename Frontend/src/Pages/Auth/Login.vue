<script setup>
import { ref } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { 
    EnvelopeIcon, 
    LockClosedIcon, 
    ArrowRightIcon,
    ExclamationCircleIcon,
    EyeIcon,
    EyeSlashIcon,
    UserIcon
} from '@heroicons/vue/24/outline'

const showPassword = ref(false)

const form = useForm({
    login: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Connexion — Mairie de Enampore" />

    <GuestLayout>
        <div class="min-h-[82vh] flex flex-col items-center justify-center p-4 font-sans">
            <!-- Carte principale -->
            <div class="w-full max-w-md bg-white rounded-[2rem] overflow-hidden relative shadow-brand-lg border border-brand-50">
                
                <!-- Bande de couleur top — dégradé CRE Kolda -->
                <div class="h-1.5 w-full" style="background: linear-gradient(90deg, #0A2903 0%, #1E690F 50%, #F0C31E 100%);"></div>

                <!-- En-tête avec logo -->
                <div class="flex flex-col items-center pt-10 pb-6 px-10 border-b border-gray-50"
                     style="background: linear-gradient(180deg, #F2F9EE 0%, #ffffff 100%);">
                    <div class="h-20 w-20 rounded-full bg-white flex items-center justify-center border-4 mb-4" style="border-color: #F0C31E; box-shadow: 0 4px 20px rgba(30,105,15,0.20);">
                        <img src="/images/logo.png" alt="Mairie de Enampore" class="h-14 w-14 object-contain" />
                    </div>
                    <h1 class="text-2xl font-black tracking-tighter mb-1" style="color: #1E690F;">Espace Officiel</h1>
                    <p class="text-xs font-bold uppercase tracking-[0.3em] text-center" style="color: #F0C31E;">Mairie de Enampore — État Civil</p>
                </div>

                <!-- Formulaire -->
                <div class="p-8 md:p-10">
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Champ Email ou Téléphone -->
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 ml-1">
                                Email ou Téléphone
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-brand-500 transition-colors">
                                    <UserIcon v-if="!form.login.includes('@')" class="h-5 w-5" />
                                    <EnvelopeIcon v-else class="h-5 w-5" />
                                </div>
                                <input 
                                    v-model="form.login"
                                    type="text"
                                    required
                                    autocomplete="username"
                                    placeholder="officier@mairie-enampore.sn"
                                    class="block w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:bg-white transition-all text-sm"
                                    style="--tw-ring-color: #1E690F;"
                                    :class="{'ring-2 ring-red-400 border-red-300 bg-red-50': form.errors.login}"
                                />
                            </div>
                            <span v-if="form.errors.login" class="text-red-500 text-xs mt-1.5 flex items-center gap-1 font-medium">
                                <ExclamationCircleIcon class="h-4 w-4 flex-shrink-0" />
                                {{ form.errors.login }}
                            </span>
                        </div>

                        <!-- Mot de passe -->
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 ml-1">
                                Mot de passe
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-brand-500 transition-colors">
                                    <LockClosedIcon class="h-5 w-5" />
                                </div>
                                <input 
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    autocomplete="current-password"
                                    placeholder="••••••••••"
                                    class="block w-full pl-12 pr-12 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white transition-all outline-none text-sm"
                                    :class="{'ring-2 ring-red-400 border-red-300 bg-red-50': form.errors.password}"
                                />
                                <button 
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-brand-500 transition-colors"
                                >
                                    <component :is="showPassword ? EyeSlashIcon : EyeIcon" class="h-5 w-5" />
                                </button>
                            </div>
                            <span v-if="form.errors.password" class="text-red-500 text-xs mt-1.5 flex items-center gap-1 font-medium">
                                <ExclamationCircleIcon class="h-4 w-4 flex-shrink-0" />
                                {{ form.errors.password }}
                            </span>
                        </div>

                        <!-- Se souvenir -->
                        <div class="flex items-center">
                            <input 
                                id="remember-me" 
                                v-model="form.remember"
                                type="checkbox" 
                                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                            />
                            <label for="remember-me" class="ml-2.5 text-sm font-semibold text-gray-600">Se souvenir de moi</label>
                        </div>

                        <!-- Bouton de connexion -->
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-3.5 text-white rounded-xl font-black text-sm shadow-brand hover:shadow-brand-lg transition-all duration-200 flex items-center justify-center gap-2 group disabled:opacity-60 disabled:cursor-not-allowed"
                            style="background: linear-gradient(135deg, #1E690F 0%, #3D9426 100%);"
                        >
                            <svg v-if="form.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ form.processing ? 'Authentification...' : 'Se connecter' }}</span>
                            <ArrowRightIcon v-if="!form.processing" class="h-4 w-4 group-hover:translate-x-1 transition-transform duration-200" />
                        </button>
                    </form>
                </div>

                <!-- Pied de carte -->
                <div class="px-10 pb-8 text-center">
                    <p class="text-[10px] text-gray-400 font-medium">
                        Accès réservé aux officiers d'état civil autorisés
                    </p>
                    <p class="text-[9px] font-black text-gray-300 uppercase tracking-[0.3em] mt-1">
                        République du Sénégal — Commune de Enampore
                    </p>
                </div>
            </div>

            <!-- Badge de sécurité -->
            <div class="mt-6 flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-brand-100 shadow-sm">
                <div class="h-2 w-2 rounded-full bg-green-400 animate-pulse"></div>
                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Connexion sécurisée SSL</span>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
input:focus {
    transform: translateY(-1px);
}
</style>
