<script setup>
import { computed } from 'vue';
import { useForm, Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    UserIcon, 
    LockClosedIcon, 
    EnvelopeIcon,
    ShieldCheckIcon,
    ArrowLeftIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    user: Object,
    roles: Array,
    is_edit: Boolean,
    back_url: String,
    store_url: String,
    update_url: String,
});

const title = computed(() => props.is_edit ? "Modifier l'utilisateur" : "Nouvel Utilisateur");

const page = usePage();
const isCurrentUser = computed(() => props.is_edit && Number(props.user?.id) === Number(page.props.auth?.user?.id));

const form = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
    password: '',
    password_confirmation: '',
    role: props.user?.roles?.[0]?.name || '',
});

const submit = () => {
    if (props.is_edit) {
        form.put(props.update_url);
    } else {
        form.post(props.store_url);
    }
};
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="back_url" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-500" />
                </Link>
                <h2 class="font-black text-2xl text-gray-900 tracking-tight">{{ title }}</h2>
            </div>
        </template>

        <div class="max-w-3xl mx-auto pb-20 mt-8">
            <form @submit.prevent="submit" class="space-y-8">
                <!-- Profile Section -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black uppercase tracking-widest mb-6 flex items-center gap-2" style="color: #1E690F;">
                        <UserIcon class="h-4 w-4" />
                        Informations de Profil
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1">Nom complet</label>
                            <div class="relative">
                                <UserIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                                <input v-model="form.name" type="text" class="w-full pl-12 pr-4 py-3 rounded-2xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold transition-all" required />
                            </div>
                            <p v-if="form.errors.name" class="mt-2 text-xs text-red-600 font-bold uppercase tracking-widest">{{ form.errors.name }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1">Adresse Email</label>
                            <div class="relative">
                                <EnvelopeIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                                <input v-model="form.email" type="email" class="w-full pl-12 pr-4 py-3 rounded-2xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                            </div>
                            <p v-if="form.errors.email" class="mt-2 text-xs text-red-600 font-bold uppercase tracking-widest">{{ form.errors.email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Role Section -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black uppercase tracking-widest mb-6 flex items-center gap-2" style="color: #1E690F;">
                        <ShieldCheckIcon class="h-4 w-4" />
                        Rôles & Permissions
                    </h3>
                    <div class="space-y-4">
                        <div v-if="isCurrentUser" class="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center gap-3">
                            <ShieldCheckIcon class="h-5 w-5 text-amber-600 flex-shrink-0" />
                            <span class="text-xs font-semibold text-amber-800">
                                En tant qu'utilisateur connecté, vous ne pouvez pas modifier votre propre rôle.
                            </span>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Assigner un rôle</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <label v-for="role in roles" :key="role" 
                                       class="relative flex flex-col items-center p-4 rounded-2xl border-2 transition-all"
                                       :class="[
                                           form.role === role ? 'border-[#1E690F] bg-green-50/50' : 'border-gray-200 bg-white',
                                           isCurrentUser ? 'cursor-not-allowed opacity-60' : 'cursor-pointer hover:bg-gray-50'
                                       ]">
                                    <input type="radio" v-model="form.role" :value="role" :disabled="isCurrentUser" class="sr-only" required />
                                    <span class="text-[10px] font-black uppercase tracking-widest text-center" :class="form.role === role ? 'text-[#1E690F]' : 'text-gray-400'">{{ role }}</span>
                                </label>
                            </div>
                            <p v-if="form.errors.role" class="mt-2 text-xs text-red-600 font-bold uppercase tracking-widest">{{ form.errors.role }}</p>
                        </div>
                    </div>
                </div>

                <!-- Security Section -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black uppercase tracking-widest mb-6 flex items-center gap-2" style="color: #1E690F;">
                        <LockClosedIcon class="h-4 w-4" />
                        Sécurité du compte
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1">Mot de passe {{ is_edit ? '(Optionnel)' : '' }}</label>
                            <input v-model="form.password" type="password" class="w-full px-4 py-3 rounded-2xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" :required="!is_edit" />
                            <p v-if="form.errors.password" class="mt-2 text-xs text-red-600 font-bold uppercase tracking-widest">{{ form.errors.password }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1">Confirmer</label>
                            <input v-model="form.password_confirmation" type="password" class="w-full px-4 py-3 rounded-2xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" :required="!is_edit" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4">
                    <Link :href="back_url" class="px-8 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black text-xs uppercase hover:bg-gray-200 transition-all">Annuler</Link>
                    <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center px-10 py-4 text-white rounded-2xl font-black text-xs uppercase shadow-2xl shadow-green-100 hover:bg-[#185709] transition-all active:scale-95 disabled:bg-gray-400"
                            style="background-color: #1E690F;">
                        <CheckCircleIcon class="w-5 h-5 mr-3 stroke-[3]" />
                        {{ is_edit ? 'Mettre à jour' : 'Enregistrer l\'utilisateur' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
