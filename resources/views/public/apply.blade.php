@extends('layouts.public')

@section('title', 'පෙරපාසල් ළමා ඇතුළත් කිරීම් අයදුම්පත - ' . $school->name)

@section('content')
<div class="max-w-3xl mx-auto py-6 sm:py-10" x-data="preSchoolApplication()">

    <!-- Form Header Banner -->
    <div class="text-center mb-8">
        <span class="inline-block px-3.5 py-1 rounded-full text-xs font-bold bg-amber-400/10 text-amber-300 border border-amber-400/20 mb-3 shadow-xs">
            අධ්‍යයන වර්ෂය {{ $school->academic_year }} &bull; නව ළමුන් බඳවා ගැනීම් (New Admissions)
        </span>
        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">පෙරපාසල් ළමා ඇතුළත් කිරීම් අයදුම්පත</h1>
        <p class="text-xs sm:text-sm font-semibold text-amber-300/90 mt-1">{{ $school->name }}</p>
        <p class="text-xs sm:text-sm text-slate-400 mt-2 max-w-xl mx-auto leading-relaxed">
            කරුණාකර ඔබගේ දරුවාගේ තොරතුරු පහතින් සම්පූර්ණ කරන්න. විදුහල්පතිතුමිය විසින් අයදුම්පත අනුමත කළ පසු, තේරී පත්වීමේ පණිවිඩය, අවශ්‍ය ද්‍රව්‍ය ලේඛනය සහ දරුවාගේ නිල ඩිජිටල් QR හැඳුනුම්පත් පාස්පත ඔබගේ WhatsApp අංකයට ලැබෙනු ඇත.
        </p>
    </div>

    @if(session('error'))
        <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs font-semibold flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation text-rose-400 text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Main Application Form Card -->
    <div class="rounded-3xl bg-slate-800/80 border border-slate-700/80 p-6 sm:p-10 shadow-2xl backdrop-blur-md">
        <form action="{{ route('public.apply.store', ['token' => $school->registration_token]) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- STEP 1: Child Information -->
            <div class="space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-700/60 text-amber-300 font-extrabold text-sm uppercase tracking-wider">
                    <i class="fa-solid fa-child-reaching text-base"></i>
                    <span>1. දරුවාගේ පෞද්ගලික තොරතුරු (Child's Personal Information)</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            දරුවාගේ සම්පූර්ණ නම (Child's Full Name) <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" name="child_full_name" value="{{ old('child_full_name') }}" required 
                               placeholder="උදා: කවීන් සෙනුල්‍ය ප්‍රනාන්දු / Kaveen Senulya Fernando"
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            ගෙදරට අමතන නම (Calling / Nick Name)
                        </label>
                        <input type="text" name="child_calling_name" value="{{ old('child_calling_name') }}" 
                               placeholder="උදා: කවීන් / Kaveen"
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            උපන් දිනය (Date of Birth) <span class="text-rose-400">*</span>
                        </label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            ස්ත්‍රී / පුරුෂ භාවය (Gender) <span class="text-rose-400">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer border-2 rounded-xl p-3 flex items-center justify-center gap-2.5 transition-all text-center"
                                   :class="gender === 'male' ? 'border-cyan-400 bg-cyan-950/40 text-cyan-200 font-bold' : 'border-slate-700 bg-slate-900 text-slate-400'">
                                <input type="radio" name="gender" value="male" x-model="gender" class="sr-only">
                                <i class="fa-solid fa-mars text-cyan-400"></i>
                                <span class="text-xs">පිරිමි (Boy)</span>
                            </label>

                            <label class="cursor-pointer border-2 rounded-xl p-3 flex items-center justify-center gap-2.5 transition-all text-center"
                                   :class="gender === 'female' ? 'border-pink-400 bg-pink-950/40 text-pink-200 font-bold' : 'border-slate-700 bg-slate-900 text-slate-400'">
                                <input type="radio" name="gender" value="female" x-model="gender" class="sr-only">
                                <i class="fa-solid fa-venus text-pink-400"></i>
                                <span class="text-xs">ගැහැණු (Girl)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            ඇතුළත් කරන පන්තිය (Applying Level) <span class="text-rose-400">*</span>
                        </label>
                        <select name="target_grade_level" required
                                class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 transition-all">
                            <option value="Playgroup">Playgroup (වයස අවු. 1.5 - 2.5)</option>
                            <option value="Nursery" selected>Nursery (වයස අවු. 2.5 - 3.5)</option>
                            <option value="LKG">LKG - Lower Kindergarten (වයස අවු. 3.5 - 4.5)</option>
                            <option value="UKG">UKG - Upper Kindergarten (වයස අවු. 4.5 - 5.5)</option>
                            <option value="Daycare">Daycare / දිවා සුරැකුම් මධ්‍යස්ථානය</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            රුධිර ගණය (Blood Group - Optional)
                        </label>
                        <input type="text" name="blood_group" value="{{ old('blood_group') }}" placeholder="උදා: O+, A+, B+, AB+"
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 transition-all">
                    </div>

                    <!-- Child Photo Upload with Client Compression -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            දරුවාගේ ඡායාරූපය (Child's Photo - For QR ID Card)
                        </label>
                        <div class="flex items-center gap-4 p-3.5 rounded-2xl border border-dashed border-slate-700 bg-slate-900/60">
                            <div class="w-16 h-16 rounded-xl overflow-hidden bg-slate-800 border-2 border-amber-400/40 shrink-0 flex items-center justify-center">
                                <template x-if="photoPreview">
                                    <img :src="photoPreview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!photoPreview">
                                    <i class="fa-solid fa-camera text-slate-600 text-xl"></i>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <input type="file" name="photo" id="child_photo" accept="image/*" class="sr-only"
                                       @change="compressPhoto($event.target)">
                                <input type="hidden" name="photo_base64" id="photo_base64">
                                <label for="child_photo" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold border border-slate-700 cursor-pointer transition-colors shadow-xs">
                                    <i class="fa-solid fa-upload text-amber-400"></i>
                                    <span>ඡායාරූපයක් / සෙල්ෆියක් උඩුගත කරන්න (Upload Photo)</span>
                                </label>
                                <p class="text-[11px] text-slate-400 mt-1">මෙම ඡායාරූපය ස්වයංක්‍රීයව දරුවාගේ මුද්‍රණය කළ හැකි 3.7" &times; 2.6" QR හැඳුනුම්පත් පාස්පතට සකස් වේ.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 2: Parents & Guardian Information -->
            <div class="space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-700/60 text-cyan-300 font-extrabold text-sm uppercase tracking-wider">
                    <i class="fa-solid fa-people-roof text-base"></i>
                    <span>2. දෙමාපියන් සහ සබඳතා තොරතුරු (Parents & Contact Information)</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            පියාගේ නම (Father's Name)
                        </label>
                        <input type="text" name="father_name" value="{{ old('father_name') }}" placeholder="උදා: සුනිල් ප්‍රනාන්දු"
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            පියාගේ දුරකථන අංකය (Father's Phone)
                        </label>
                        <input type="tel" name="father_phone" value="{{ old('father_phone') }}" placeholder="උදා: 077 111 2222"
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            මවගේ නම (Mother's Name)
                        </label>
                        <input type="text" name="mother_name" value="{{ old('mother_name') }}" placeholder="උදා: කුමාරි ප්‍රනාන්දු"
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            මවගේ දුරකථන අංකය (Mother's Phone)
                        </label>
                        <input type="tel" name="mother_phone" value="{{ old('mother_phone') }}" placeholder="උදා: 077 333 4444"
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-amber-300 uppercase tracking-wider mb-1.5">
                            <i class="fa-brands fa-whatsapp text-emerald-400 mr-1"></i> WhatsApp අංකය (පණිවිඩ සහ QR පාස්පත සඳහා) <span class="text-rose-400">*</span>
                        </label>
                        <input type="tel" name="whatsapp_number" value="{{ old('whatsapp_number') }}" required placeholder="උදා: 077 123 4567"
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-emerald-500/60 text-white placeholder-slate-500 text-sm focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20 transition-all">
                        <p class="text-[11px] text-slate-400 mt-1">තේරී පත්වීමේ පණිවිඩය සහ දරුවාගේ QR හැඳුනුම්පත් පාස්පත මෙම WhatsApp අංකයට යොමු කෙරේ.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            විද්‍යුත් තැපෑල (Email Address - Optional)
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="උදා: parent@gmail.com"
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition-all">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            ස්ථිර ලිපිනය (Permanent Home Address) <span class="text-rose-400">*</span>
                        </label>
                        <textarea name="home_address" rows="2" required placeholder="නිවසේ ලිපිනය, නගරය"
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition-all">{{ old('home_address') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Safety, Pickup & Medical -->
            <div class="space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-700/60 text-emerald-300 font-extrabold text-sm uppercase tracking-wider">
                    <i class="fa-solid fa-shield-heart text-base"></i>
                    <span>3. දරුවාගේ ආරක්ෂාව සහ සෞඛ්‍ය තොරතුරු (Child Safety & Pickup Authorization)</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            දරුවා රැගෙන යාමට බලයලත් පුද්ගලයින් (Authorized Pickup Persons)
                        </label>
                        <textarea name="authorized_pickup_persons" rows="2" placeholder="උදා: සීයා (සිල්වා මහතා - 0775556666), වෑන් රථ රියදුරු (පෙරේරා මහතා)"
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20 transition-all">{{ old('authorized_pickup_persons') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            ආසාත්මිකතා හෝ විශේෂ සෞඛ්‍ය සටහන් (Allergies / Medical Notes)
                        </label>
                        <textarea name="medical_notes" rows="2" placeholder="උදා: රටකජු ආසාත්මිකතාව, ඇදුම රෝගය (අවශ්‍ය නම් ඉන්හේලර් භාවිත කෙරේ)"
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20 transition-all">{{ old('medical_notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-6 border-t border-slate-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-xs text-slate-400">
                    <i class="fa-solid fa-shield-check text-emerald-400 mr-1"></i>
                    අයදුම්පත ඉදිරිපත් කිරීමෙන්, ඉහත සඳහන් සියලු තොරතුරු සත්‍ය සහ නිවැරදි බව ඔබ සහතික කර සිටියි.
                </p>

                <button type="submit" 
                        class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-sm shadow-xl shadow-amber-500/20 flex items-center justify-center gap-2 transition-all cursor-pointer">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>අයදුම්පත යොමු කරන්න (Submit Application)</span>
                </button>
            </div>
        </form>
    </div>

</div>

<script>
function preSchoolApplication() {
    return {
        gender: '{{ old('gender', 'male') }}',
        photoPreview: null,
        compressPhoto(fileInput) {
            if (!fileInput.files || !fileInput.files[0]) return;
            const file = fileInput.files[0];
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const maxDim = 800;
                    let width = img.width;
                    let height = img.height;
                    if (width > maxDim || height > maxDim) {
                        if (width > height) {
                            height = Math.round((height * maxDim) / width);
                            width = maxDim;
                        } else {
                            width = Math.round((width * maxDim) / height);
                            height = maxDim;
                        }
                    }
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                    this.photoPreview = dataUrl;
                    document.getElementById('photo_base64').value = dataUrl;
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    };
}
</script>
@endsection
