<script setup>
import { computed } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
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
                    <h3 class="text-xs font-black text-blue-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <UserIcon class="h-4 w-4" />
                        Informations de Profil
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nom complet</label>
                            <div class="relative">
                                <UserIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                                <input v-model="form.name" type="text" class="w-full pl-12 pr-4 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-blue-600 font-bold transition-all" required />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Adresse Email</label>
                            <div class="relative">
                                <EnvelopeIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                                <input v-model="form.email" type="email" class="w-full pl-12 pr-4 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white font-bold" required />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Role Section -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black text-purple-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <ShieldCheckIcon class="h-4 w-4" />
                        Rôles & Permissions
                    </h3>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Assigner un rôle</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <label v-for="role in roles" :key="role" 
                                   class="relative flex flex-col items-center p-4 rounded-2xl border-2 cursor-pointer transition-all hover:bg-gray-50"
                                   :class="form.role === role ? 'border-blue-600 bg-blue-50' : 'border-gray-100 bg-white'">
                                <input type="radio" v-model="form.role" :value="role" class="sr-only" required />
                                <span class="text-[10px] font-black uppercase tracking-widest text-center" :class="form.role === role ? 'text-blue-900' : 'text-gray-400'">{{ role }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Security Section -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black text-red-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <LockClosedIcon class="h-4 w-4" />
                        Sécurité du compte
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Mot de passe {{ is_edit ? '(Optionnel)' : '' }}</label>
                            <input v-model="form.password" type="password" class="w-full px-4 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white font-bold" :required="!is_edit" />
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Confirmer</label>
                            <input v-model="form.password_confirmation" type="password" class="w-full px-4 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white font-bold" :required="!is_edit" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4">
                    <Link :href="back_url" class="px-8 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black text-xs uppercase hover:bg-gray-200 transition-all">Annuler</Link>
                    <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center px-10 py-4 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase shadow-2xl shadow-blue-200 hover:bg-blue-700 transition-all active:scale-95 disabled:bg-gray-400">
                        <CheckCircleIcon class="w-5 h-5 mr-3 stroke-[3]" />
                        {{ is_edit ? 'Mettre à jour' : 'Enregistrer l\'utilisateur' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
