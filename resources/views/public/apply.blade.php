@extends('layouts.public')

@section('title', 'Online Admission Application - ' . $school->name)

@section('content')
<div class="max-w-3xl mx-auto py-6 sm:py-10" x-data="preSchoolApplication()">

    <!-- Form Header Banner -->
    <div class="text-center mb-8">
        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-amber-400/10 text-amber-300 border border-amber-400/20 mb-3">
            Academic Year {{ $school->academic_year }} &bull; New Admissions
        </span>
        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Pre-School Admission Application</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-xl mx-auto">
            Please fill in your child's details below. Upon approval by the Principal, you will receive confirmation and your child's official QR code ID pass via WhatsApp.
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
        <form action="{{ route('public.apply.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- STEP 1: Child Information -->
            <div class="space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-700/60 text-amber-300 font-extrabold text-sm uppercase tracking-wider">
                    <i class="fa-solid fa-child-reaching text-base"></i>
                    <span>1. Child's Personal Information</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Child's Full Name *</label>
                        <input type="text" name="child_full_name" value="{{ old('child_full_name') }}" required placeholder="e.g. Kaveen Senulya Fernando"
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Calling / Nick Name</label>
                        <input type="text" name="child_calling_name" value="{{ old('child_calling_name') }}" placeholder="e.g. Kaveen"
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Date of Birth *</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Gender *</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer border-2 rounded-xl p-3 flex items-center gap-2.5 transition-all"
                                   :class="gender === 'male' ? 'border-cyan-400 bg-cyan-950/40 text-cyan-200' : 'border-slate-700 bg-slate-900 text-slate-400'">
                                <input type="radio" name="gender" value="male" x-model="gender" class="sr-only">
                                <i class="fa-solid fa-mars"></i>
                                <span class="text-xs font-bold">Boy / Male</span>
                            </label>

                            <label class="cursor-pointer border-2 rounded-xl p-3 flex items-center gap-2.5 transition-all"
                                   :class="gender === 'female' ? 'border-pink-400 bg-pink-950/40 text-pink-200' : 'border-slate-700 bg-slate-900 text-slate-400'">
                                <input type="radio" name="gender" value="female" x-model="gender" class="sr-only">
                                <i class="fa-solid fa-venus"></i>
                                <span class="text-xs font-bold">Girl / Female</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Applying For Program / Level *</label>
                        <select name="target_grade_level" required
                                class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 transition-all">
                            <option value="Playgroup">Playgroup (1.5 - 2.5 Years)</option>
                            <option value="Nursery" selected>Nursery (2.5 - 3.5 Years)</option>
                            <option value="LKG">LKG - Lower Kindergarten (3.5 - 4.5 Years)</option>
                            <option value="UKG">UKG - Upper Kindergarten (4.5 - 5.5 Years)</option>
                            <option value="Daycare">Daycare / Extended Care</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Blood Group (Optional)</label>
                        <input type="text" name="blood_group" value="{{ old('blood_group') }}" placeholder="e.g. O+, A+, B+"
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 transition-all">
                    </div>

                    <!-- Child Photo Upload with Client Compression -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Child's Photo (For Pre-School QR ID Card)</label>
                        <div class="flex items-center gap-4 p-3 rounded-2xl border border-dashed border-slate-700 bg-slate-900/60">
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
                                <label for="child_photo" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold border border-slate-700 cursor-pointer transition-colors">
                                    <i class="fa-solid fa-upload text-amber-400"></i>
                                    <span>Upload Child Photo / Selfie</span>
                                </label>
                                <p class="text-[11px] text-slate-400 mt-1">Photo will be automatically formatted onto the child's printable 3.7" &times; 2.6" ID Card.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 2: Parents & Guardian Information -->
            <div class="space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-700/60 text-cyan-300 font-extrabold text-sm uppercase tracking-wider">
                    <i class="fa-solid fa-people-roof text-base"></i>
                    <span>2. Parents & Contact Information</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Father's Name</label>
                        <input type="text" name="father_name" value="{{ old('father_name') }}" placeholder="e.g. Sunil Fernando"
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Father's Contact / Phone</label>
                        <input type="tel" name="father_phone" value="{{ old('father_phone') }}" placeholder="e.g. 077 111 2222"
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Mother's Name</label>
                        <input type="text" name="mother_name" value="{{ old('mother_name') }}" placeholder="e.g. Kumari Fernando"
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Mother's Contact / Phone</label>
                        <input type="tel" name="mother_phone" value="{{ old('mother_phone') }}" placeholder="e.g. 077 333 4444"
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-amber-300 uppercase tracking-wider mb-1.5">
                            <i class="fa-brands fa-whatsapp text-emerald-400 mr-1"></i> WhatsApp Number (For Notifications) *
                        </label>
                        <input type="tel" name="whatsapp_number" value="{{ old('whatsapp_number') }}" required placeholder="e.g. 077 123 4567"
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-emerald-500/50 text-white placeholder-slate-500 text-sm focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20 transition-all">
                        <p class="text-[11px] text-slate-400 mt-1">Admission updates and child's QR Pass will be dispatched to this number.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="e.g. parent@gmail.com"
                               class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition-all">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Permanent Home Address *</label>
                        <textarea name="home_address" rows="2" required placeholder="Street address, City"
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition-all">{{ old('home_address') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Safety, Pickup & Medical -->
            <div class="space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-700/60 text-emerald-300 font-extrabold text-sm uppercase tracking-wider">
                    <i class="fa-solid fa-shield-heart text-base"></i>
                    <span>3. Child Safety & Pickup Authorization</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Authorized Persons for Pickup</label>
                        <textarea name="authorized_pickup_persons" rows="2" placeholder="e.g. Grandfather (Mr. Silva - 0775556666), Van Driver (Mr. Perera)"
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20 transition-all">{{ old('authorized_pickup_persons') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Allergies / Medical Notes (If Any)</label>
                        <textarea name="medical_notes" rows="2" placeholder="e.g. Peanut allergy, Asthma, takes inhaler if needed"
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20 transition-all">{{ old('medical_notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-6 border-t border-slate-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-xs text-slate-400">By submitting, you declare that all provided details are true and accurate.</p>

                <button type="submit" 
                        class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-sm shadow-xl shadow-amber-500/20 flex items-center justify-center gap-2 transition-all cursor-pointer">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Submit Application</span>
                </button>
            </div>
        </form>
    </div>

</div>

<script>
function preSchoolApplication() {
    return {
        gender: 'male',
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

