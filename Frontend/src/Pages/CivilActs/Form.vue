<script setup>
import { computed } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    CalendarIcon, 
    MapPinIcon, 
    IdentificationIcon,
    UserGroupIcon,
    PlusCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    act: Object,
    type: String,
    is_edit: Boolean,
});

const title = computed(() => {
    if (props.is_edit) return `Modifier l'acte (${props.type})`;
    switch (props.type) {
        case 'naissance': return 'Déclaration de Naissance';
        case 'mariage': return 'Célébration de Mariage';
        case 'deces': return 'Déclaration de Décès';
        default: return 'Nouvel Acte';
    }
});

const form = useForm({
    // Common
    officer_comments: props.act?.officer_comments || '',
    
    // Naissance
    first_name: props.act?.first_name || '',
    last_name: props.act?.last_name || '',
    date_of_birth: props.act?.date_of_birth || '',
    place_of_birth: props.act?.place_of_birth || '',
    gender: props.act?.gender || 'M',
    father_name: props.act?.father_name || '',
    mother_name: props.act?.mother_name || '',
    parents_metadata: props.act?.parents_metadata || {
        father_profession: '',
        mother_profession: '',
        residence: ''
    },

    // Mariage
    husband_first_name: props.act?.husband_first_name || '',
    husband_last_name: props.act?.husband_last_name || '',
    wife_first_name: props.act?.wife_first_name || '',
    wife_last_name: props.act?.wife_last_name || '',
    marriage_date: props.act?.marriage_date || '',
    marriage_place: props.act?.marriage_place || '',
    witnesses_metadata: props.act?.witnesses_metadata || {
        witness1_name: '',
        witness2_name: ''
    },

    // Deces
    deceased_first_name: props.act?.deceased_first_name || '',
    deceased_last_name: props.act?.deceased_last_name || '',
    date_of_death: props.act?.date_of_death || '',
    place_of_death: props.act?.place_of_death || '',
    cause_of_death: props.act?.cause_of_death || '',
});

const submit = () => {
    if (props.is_edit) {
        form.patch(`/acts/${props.type}/${props.act.id}`);
    } else {
        form.post(`/acts/${props.type}`);
    }
};
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-black text-2xl text-gray-900 tracking-tight">{{ title }}</h2>
        </template>

        <div class="max-w-4xl mx-auto pb-20">
            <form @submit.prevent="submit" class="space-y-8">
                
                <!-- Section 1: Informations de Base -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black text-blue-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <IdentificationIcon class="h-4 w-4" />
                        Informations d'Identification
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-if="type === 'naissance'" class="space-y-6 col-span-full">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Prénoms de l'enfant</label>
                                    <input v-model="form.first_name" type="text" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-blue-600 focus:border-blue-600 font-bold transition-all" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Nom (Patronyme)</label>
                                    <input v-model="form.last_name" type="text" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-blue-600 focus:border-blue-600 font-bold transition-all" required />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Sexe</label>
                                    <select v-model="form.gender" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-blue-600 font-bold">
                                        <option value="M">Masculin</option>
                                        <option value="F">Féminin</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Date de Naissance</label>
                                    <input v-model="form.date_of_birth" type="date" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white font-bold" required />
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Lieu de Naissance</label>
                                <input v-model="form.place_of_birth" type="text" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white font-bold" required />
                            </div>
                        </div>

                        <div v-if="type === 'mariage'" class="space-y-6 col-span-full">
                            <div class="grid grid-cols-2 gap-6">
                                <div class="p-6 bg-blue-50/50 rounded-2xl border border-blue-100 shadow-sm transition-all hover:shadow-md">
                                    <h4 class="text-[10px] font-black text-blue-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-blue-600 rounded-full"></div>
                                        Épou (Mari)
                                    </h4>
                                    <div class="space-y-4">
                                        <input v-model="form.husband_first_name" placeholder="Prénoms" class="w-full rounded-xl border-white focus:ring-blue-600" required />
                                        <input v-model="form.husband_last_name" placeholder="Nom" class="w-full rounded-xl border-white focus:ring-blue-600" required />
                                    </div>
                                </div>
                                <div class="p-6 bg-pink-50/50 rounded-2xl border border-pink-100 shadow-sm transition-all hover:shadow-md">
                                    <h4 class="text-[10px] font-black text-pink-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                                         <div class="w-1.5 h-1.5 bg-pink-600 rounded-full"></div>
                                        Épouse (Femme)
                                    </h4>
                                    <div class="space-y-4">
                                        <input v-model="form.wife_first_name" placeholder="Prénoms" class="w-full rounded-xl border-white focus:ring-pink-600" required />
                                        <input v-model="form.wife_last_name" placeholder="Nom" class="w-full rounded-xl border-white focus:ring-pink-600" required />
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Date du Mariage</label>
                                    <input v-model="form.marriage_date" type="date" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Lieu de Célébration</label>
                                    <input v-model="form.marriage_place" type="text" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white" required />
                                </div>
                            </div>
                        </div>

                        <div v-if="type === 'deces'" class="space-y-6 col-span-full">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Prénoms du défunt</label>
                                    <input v-model="form.deceased_first_name" type="text" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Nom du défunt</label>
                                    <input v-model="form.deceased_last_name" type="text" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white" required />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Date du Décès</label>
                                    <input v-model="form.date_of_death" type="date" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Lieu du Décès</label>
                                    <input v-model="form.place_of_death" type="text" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white" required />
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Cause du Décès (Optionnel)</label>
                                <textarea v-model="form.cause_of_death" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Métadonnées Spécifiques -->
                <div v-if="type === 'naissance'" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black text-blue-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <UserGroupIcon class="h-4 w-4" />
                        Filiation & Détails Parents
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Prénoms & Nom du Père</label>
                            <input v-model="form.father_name" type="text" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Profession du Père</label>
                            <input v-model="form.parents_metadata.father_profession" type="text" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white" />
                        </div>
                         <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Prénoms & Nom de la Mère</label>
                            <input v-model="form.mother_name" type="text" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Profession de la Mère</label>
                            <input v-model="form.parents_metadata.mother_profession" type="text" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white" />
                        </div>
                        <div class="col-span-full">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Domicile des parents</label>
                            <input v-model="form.parents_metadata.residence" type="text" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white" />
                        </div>
                    </div>
                </div>

                <div v-if="type === 'mariage'" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black text-blue-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <UserGroupIcon class="h-4 w-4" />
                        Témoins de l'acte
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Témoin 1 (Prénoms & Nom)</label>
                            <input v-model="form.witnesses_metadata.witness1_name" type="text" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Témoin 2 (Prénoms & Nom)</label>
                            <input v-model="form.witnesses_metadata.witness2_name" type="text" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white" />
                        </div>
                    </div>
                </div>

                <!-- Section 3: Remarques de l'Officier -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black text-blue-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <PlusCircleIcon class="h-4 w-4" />
                        Observations de l'Officier
                    </h3>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Commentaires officiels</label>
                        <textarea v-model="form.officer_comments" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:bg-white" rows="3" placeholder="Notes additionnelles, mentions marginales..."></textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-6 gap-4">
                     <Link :href="`/acts/${type}`" class="inline-flex items-center justify-center px-10 py-4 bg-white border border-gray-200 rounded-2xl font-black text-xs text-gray-500 uppercase tracking-widest hover:bg-gray-50 transition-all">
                        Annuler
                    </Link>
                    <button type="submit" 
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center px-10 py-4 bg-blue-600 border border-transparent rounded-2xl font-black text-xs text-white uppercase tracking-widest hover:bg-blue-700 shadow-2xl shadow-blue-200 transition-all active:scale-95 disabled:bg-gray-400">
                        <PlusCircleIcon v-if="!form.processing" class="w-5 h-5 mr-2 stroke-[3]" />
                        {{ form.processing ? 'Chargement...' : (is_edit ? 'Mettre à jour l\'Acte' : 'Enregistrer l\'Acte Officiel') }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
