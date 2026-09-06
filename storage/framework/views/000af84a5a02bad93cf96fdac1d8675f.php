<div>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)"
         class="fixed top-5 right-5 z-[200] flex items-center gap-3 bg-white border border-green-200 text-green-800 text-sm font-medium rounded-xl shadow-lg px-5 py-3">
        <i class="fa-solid fa-circle-check text-green-500"></i>
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage your buyer profile, media, and locations.</p>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile?->isComplete()): ?>
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-green-50 text-green-700 border border-green-200">
            <i class="fa-solid fa-circle-check"></i> Profile Complete
        </span>
    <?php else: ?>
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
            <i class="fa-solid fa-clock"></i> Draft
        </span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>


<div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 mb-6">
    <div class="flex items-center gap-4">
        
        <div class="relative flex-shrink-0">
            <img src="<?php echo e($profile?->logo ? asset('storage/'.$profile->logo) : 'https://ui-avatars.com/api/?name='.urlencode($profile?->display_name ?? 'B').'&background=e0e7ff&color=4f46e5&size=80'); ?>"
                 class="w-16 h-16 rounded-2xl object-cover border border-gray-200 shadow-sm" alt="Logo">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile?->profile_photo): ?>
                <img src="<?php echo e(asset('storage/'.$profile->profile_photo)); ?>"
                     class="w-7 h-7 rounded-full border-2 border-white absolute -bottom-1 -right-1 object-cover shadow" alt="">
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="flex-1 min-w-0">
            <p class="text-lg font-bold text-gray-900 truncate"><?php echo e($profile?->display_name ?? 'Your Business Name'); ?></p>
            <p class="text-sm text-gray-500 truncate"><?php echo e($profile?->email ?? $account->primaryOwner?->email); ?></p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile?->country_id): ?>
                <p class="text-xs text-gray-400 mt-0.5">
                    <i class="fa-solid fa-location-dot mr-1 text-indigo-400"></i>
                    <?php echo e(collect([$profile->city?->name, $profile->country?->name])->filter()->implode(', ')); ?>

                </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="hidden sm:flex items-center gap-5 flex-shrink-0 border-l border-gray-100 pl-5">
            <div class="text-center">
                <p class="text-xl font-bold text-indigo-600"><?php echo e($existingGallery->count()); ?></p>
                <p class="text-xs text-gray-400">Gallery</p>
            </div>
            <div class="text-center">
                <p class="text-xl font-bold text-emerald-600"><?php echo e(count($locations)); ?></p>
                <p class="text-xs text-gray-400">Locations</p>
            </div>
        </div>
    </div>
</div>


<div class="space-y-3">

    
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: true }">
        <button @click="open = !open" type="button"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fa-solid fa-building text-sm"></i>
                </div>
                <div class="text-left">
                    <p class="text-sm font-semibold text-gray-900">Company Information</p>
                    <p class="text-xs text-gray-400"><?php echo e($display_name ?: 'Name, type, bio, procurement notes'); ?></p>
                </div>
            </div>
            <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
        </button>

        <div x-show="open" x-transition class="border-t border-gray-100 px-6 py-6" wire:key="section-company">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['display_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mb-3"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['buyer_type_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mb-3"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="space-y-5">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Buyer Type(s) <span class="text-red-500">*</span></label>
                    <div class="flex flex-wrap gap-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $buyerTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="inline-flex items-center gap-1.5 text-xs font-medium border rounded-full px-3 py-1.5 cursor-pointer transition-colors <?php echo e(in_array($bt->id, $buyer_type_ids) ? 'border-indigo-400 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-600 hover:border-gray-300'); ?>">
                                <input type="checkbox" wire:model.live="buyer_type_ids" value="<?php echo e($bt->id); ?>">
                                <?php echo e($bt->name); ?>

                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Display Name <span class="text-red-500">*</span></label>
                        <input wire:model="display_name" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none transition" placeholder="e.g. Acme Corp">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Organisation Name</label>
                        <input wire:model="organization_name" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none transition" placeholder="Legal entity name">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">About / Bio</label>
                    <textarea wire:model="bio" rows="3" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none transition resize-none" placeholder="Tell suppliers about your organisation..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Procurement Notes</label>
                    <textarea wire:model="procurement_info" rows="2" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none transition resize-none" placeholder="What do you typically procure?"></textarea>
                </div>
            </div>

            <div class="flex justify-end mt-5 pt-4 border-t border-gray-100">
                <button wire:click="saveCompany" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span wire:loading.remove wire:target="saveCompany">Save Company Info</span>
                    <span wire:loading wire:target="saveCompany">Saving...</span>
                </button>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: false }">
        <button @click="open = !open" type="button"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-sky-50 flex items-center justify-center text-sky-600">
                    <i class="fa-solid fa-address-card text-sm"></i>
                </div>
                <div class="text-left">
                    <p class="text-sm font-semibold text-gray-900">Contact Information</p>
                    <p class="text-xs text-gray-400"><?php echo e($contact_person ?: 'Person, email, phone, website'); ?></p>
                </div>
            </div>
            <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
        </button>

        <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6" wire:key="section-contact">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Person <span class="text-red-500">*</span></label>
                    <input wire:model="contact_person" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['contact_person'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Position / Title</label>
                    <input wire:model="position" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input wire:model="email" type="email" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                    <input wire:model="phone" type="tel" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Website</label>
                    <input wire:model="website" type="url" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition" placeholder="https://...">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['website'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Tax ID / VAT</label>
                    <input wire:model="tax_id" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition">
                </div>
            </div>

            <div class="flex justify-end mt-5 pt-4 border-t border-gray-100">
                <button wire:click="saveContact" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span wire:loading.remove wire:target="saveContact">Save Contact</span>
                    <span wire:loading wire:target="saveContact">Saving...</span>
                </button>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: false }">
        <button @click="open = !open" type="button"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center text-violet-600">
                    <i class="fa-solid fa-images text-sm"></i>
                </div>
                <div class="text-left">
                    <p class="text-sm font-semibold text-gray-900">Media & Branding</p>
                    <p class="text-xs text-gray-400">Logo, profile photo, gallery images</p>
                </div>
            </div>
            <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
        </button>

        <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6" wire:key="section-media">

            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Logo</label>
                    <div class="flex items-center gap-4">
                        <img src="<?php echo e($profile?->logo ? asset('storage/'.$profile->logo) : 'https://ui-avatars.com/api/?name='.urlencode($profile?->display_name ?? 'B').'&background=e0e7ff&color=4f46e5'); ?>"
                             class="w-16 h-16 rounded-xl object-contain border border-gray-200 bg-gray-50" alt="Logo">
                        <div class="flex-1">
                            <input wire:model="logo_upload" type="file" accept="image/*"
                                   class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="mt-1 text-xs text-gray-400">PNG/JPG/WEBP, max 5MB</p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['logo_upload'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logo_upload): ?>
                                <img src="<?php echo e($logo_upload->temporaryUrl()); ?>" class="mt-2 w-12 h-12 rounded-lg object-cover border border-indigo-200">
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Profile Photo</label>
                    <div class="flex items-center gap-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile?->profile_photo): ?>
                            <img src="<?php echo e(asset('storage/'.$profile->profile_photo)); ?>"
                                 class="w-16 h-16 rounded-full object-cover border border-gray-200" alt="Photo">
                        <?php else: ?>
                            <div class="w-16 h-16 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400">
                                <i class="fa-solid fa-user text-xl"></i>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="flex-1">
                            <input wire:model="profile_photo_upload" type="file" accept="image/*"
                                   class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                            <p class="mt-1 text-xs text-gray-400">PNG/JPG/WEBP, max 5MB</p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['profile_photo_upload'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile_photo_upload): ?>
                                <img src="<?php echo e($profile_photo_upload->temporaryUrl()); ?>" class="mt-2 w-12 h-12 rounded-full object-cover border border-violet-200">
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Gallery Images</label>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($existingGallery->isNotEmpty()): ?>
                    <div class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-7 gap-2 mb-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $existingGallery; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="relative group aspect-square">
                                <img src="<?php echo e(asset('storage/'.$img->image_path)); ?>"
                                     class="w-full h-full object-cover rounded-xl border border-gray-200" alt="">
                                <button wire:click="removeExistingGalleryImage(<?php echo e($img->id); ?>)"
                                        wire:confirm="Remove this image?"
                                        class="absolute top-1 right-1 w-5 h-5 rounded-full bg-red-500 text-white text-[9px] opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center shadow cursor-pointer">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($gallery_files) > 0): ?>
                    <div class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-7 gap-2 mb-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $gallery_files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="relative group aspect-square">
                                <img src="<?php echo e($file->temporaryUrl()); ?>"
                                     class="w-full h-full object-cover rounded-xl border-2 border-indigo-300" alt="">
                                <button wire:click="removeGalleryFile(<?php echo e($i); ?>)"
                                        class="absolute top-1 right-1 w-5 h-5 rounded-full bg-red-500 text-white text-[9px] opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center shadow cursor-pointer">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                                <span class="absolute bottom-1 left-1 text-[8px] font-bold text-white bg-indigo-500 px-1 rounded">NEW</span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <label class="flex items-center gap-3 px-4 py-3 bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition-colors">
                    <i class="fa-solid fa-cloud-arrow-up text-gray-400 text-xl"></i>
                    <span class="text-sm text-gray-500">Click to add gallery images (max 20)</span>
                    <input wire:model="new_gallery_files" type="file" accept="image/*" multiple class="hidden">
                </label>
                <p class="mt-1 text-xs text-gray-400">JPG, PNG or WEBP • Up to 5MB each • Click "Save Media" to upload</p>
            </div>

            <div class="flex justify-end mt-6 pt-4 border-t border-gray-100">
                <button wire:click="saveMedia" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span wire:loading.remove wire:target="saveMedia">Save Media</span>
                    <span wire:loading wire:target="saveMedia">Uploading...</span>
                </button>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: false }">
        <button @click="open = !open" type="button"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-pink-50 flex items-center justify-center text-pink-600">
                    <i class="fa-solid fa-share-nodes text-sm"></i>
                </div>
                <div class="text-left">
                    <p class="text-sm font-semibold text-gray-900">Social Links</p>
                    <p class="text-xs text-gray-400"><?php echo e(count($social_links) > 0 ? count($social_links).' link'.( count($social_links) > 1 ? 's' : '').' added' : 'LinkedIn, Twitter, Facebook, etc.'); ?></p>
                </div>
            </div>
            <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
        </button>

        <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6" wire:key="section-social">

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($social_links) === 0): ?>
                <p class="text-sm text-gray-400 text-center py-4">No social links added yet.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $social_links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100" wire:key="sl-<?php echo e($i); ?>">
                        <select wire:model="social_links.<?php echo e($i); ?>.platform_id"
                                class="text-sm rounded-lg border border-gray-300 px-2 py-2 bg-white focus:ring-2 focus:ring-pink-300 outline-none w-36 flex-shrink-0">
                            <option value="">Platform</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $socialPlatforms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($sp->id); ?>"><?php echo e($sp->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <input wire:model="social_links.<?php echo e($i); ?>.url" type="url"
                               class="flex-1 text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none transition"
                               placeholder="https://...">
                        <input wire:model="social_links.<?php echo e($i); ?>.label" type="text"
                               class="w-28 text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none transition hidden sm:block"
                               placeholder="Label">
                        <button wire:click="removeSocialLink(<?php echo e($i); ?>)"
                                class="w-8 h-8 flex-shrink-0 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition cursor-pointer">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["social_links.$i.url"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 -mt-2 ml-3"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <button wire:click="addSocialLink"
                    class="mt-4 flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition cursor-pointer">
                <i class="fa-solid fa-plus"></i> Add Link
            </button>

            <div class="flex justify-end mt-5 pt-4 border-t border-gray-100">
                <button wire:click="saveSocialLinks" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span wire:loading.remove wire:target="saveSocialLinks">Save Links</span>
                    <span wire:loading wire:target="saveSocialLinks">Saving...</span>
                </button>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: false }">
        <button @click="open = !open" type="button"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <i class="fa-solid fa-location-dot text-sm"></i>
                </div>
                <div class="text-left">
                    <p class="text-sm font-semibold text-gray-900">Locations</p>
                    <p class="text-xs text-gray-400"><?php echo e(count($locations) > 0 ? count($locations).' location'.( count($locations) > 1 ? 's' : '') : 'Offices, warehouses, delivery addresses'); ?></p>
                </div>
            </div>
            <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
        </button>

        <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6" wire:key="section-locations">

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="mb-4 border border-gray-200 rounded-xl overflow-hidden" wire:key="loc-<?php echo e($loc['id']); ?>"
                     x-data="{ editing: false }">
                    
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50">
                        <div class="flex items-center gap-2 min-w-0">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loc['is_primary']): ?>
                                <span class="flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700 border border-green-200">Primary</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <p class="text-sm font-semibold text-gray-900 truncate">
                                <?php echo e($loc['label'] ?: ucwords(str_replace('_', ' ', $loc['location_type']))); ?>

                            </p>
                            <p class="text-xs text-gray-500 hidden sm:block">
                                <?php echo e(collect([$loc['address_line_1'], $loc['city_name'], $loc['country_name']])->filter()->implode(', ')); ?>

                            </p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $loc['is_primary']): ?>
                                <button wire:click="makePrimaryLocation(<?php echo e($loc['id']); ?>)"
                                        class="text-xs text-emerald-600 font-medium hover:underline">Set Primary</button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <button @click="editing = !editing"
                                    class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center transition">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </button>
                            <button wire:click="removeLocation(<?php echo e($loc['id']); ?>)"
                                    wire:confirm="Remove this location?"
                                    class="w-7 h-7 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </div>
                    </div>

                    
                    <div x-show="editing" x-transition x-cloak class="p-4 border-t border-gray-100">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                                <select wire:model="locations.<?php echo e($i); ?>.location_type"
                                        class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-300 outline-none bg-white">
                                    <option value="primary">Head Office</option>
                                    <option value="registered_office">Registered Office</option>
                                    <option value="branch">Branch</option>
                                    <option value="warehouse">Warehouse</option>
                                    <option value="showroom">Showroom</option>
                                    <option value="billing">Billing Address</option>
                                    <option value="delivery">Delivery Address</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Label (optional)</label>
                                <input wire:model="locations.<?php echo e($i); ?>.label" type="text"
                                       class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-300 outline-none">
                            </div>

                            
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Country <span class="text-red-500">*</span></label>
                                <select wire:model="locations.<?php echo e($i); ?>.country_id"
                                        class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-indigo-300 outline-none">
                                    <option value="">Select country</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["locations.$i.country_id"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">State</label>
                                <select wire:model="locations.<?php echo e($i); ?>.state_id"
                                        class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-indigo-300 outline-none">
                                    <option value="">Select state</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $loc['states']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($s['id']); ?>"><?php echo e($s['name']); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">City</label>
                                <select wire:model="locations.<?php echo e($i); ?>.city_id"
                                        class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-indigo-300 outline-none">
                                    <option value="">Select city</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $loc['cities']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ci): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($ci['id']); ?>"><?php echo e($ci['name']); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Postal Code</label>
                                <input wire:model="locations.<?php echo e($i); ?>.postal_code" type="text"
                                       class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-300 outline-none">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Address Line 1 <span class="text-red-500">*</span></label>
                            <input wire:model="locations.<?php echo e($i); ?>.address_line_1" type="text"
                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-300 outline-none">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["locations.$i.address_line_1"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="mt-3">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Address Line 2</label>
                            <input wire:model="locations.<?php echo e($i); ?>.address_line_2" type="text"
                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-300 outline-none">
                        </div>

                        <div class="flex justify-end gap-2 mt-4">
                            <button @click="editing = false"
                                    class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                            <button wire:click="updateLocation(<?php echo e($loc['id']); ?>)" @click="editing = false"
                                    class="inline-flex items-center gap-1.5 text-sm font-medium px-4 py-2 rounded-lg text-white transition" style="background:var(--theme-primary)">
                                <i class="fa-solid fa-floppy-disk"></i> Save
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="mt-2" x-data="{ open: false }">
                <button @click="open = !open"
                        class="flex items-center gap-2 text-sm font-medium text-emerald-600 hover:text-emerald-800 transition mb-4">
                    <i class="fa-solid fa-plus"></i> Add New Location
                </button>

                <div x-show="open" x-transition x-cloak
                     class="border border-emerald-200 bg-emerald-50/40 rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">New Location</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Location Type <span class="text-red-500">*</span></label>
                            <select wire:model="new_location.location_type"
                                    class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                <option value="primary">Head Office</option>
                                <option value="registered_office">Registered Office</option>
                                <option value="branch">Branch</option>
                                <option value="warehouse">Warehouse</option>
                                <option value="showroom">Showroom</option>
                                <option value="billing">Billing Address</option>
                                <option value="delivery">Delivery Address</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Label (optional)</label>
                            <input wire:model="new_location.label" type="text" placeholder="e.g. Main Office"
                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-emerald-300 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Contact Name</label>
                            <input wire:model="new_location.contact_name" type="text"
                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-emerald-300 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                            <input wire:model="new_location.phone" type="tel"
                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-emerald-300 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Country <span class="text-red-500">*</span></label>
                            <select wire:model="new_location.country_id"
                                    class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                <option value="">Select country</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['new_location.country_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">State</label>
                            <select wire:model="new_location.state_id"
                                    class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-emerald-300 outline-none"
                                    <?php if(empty($new_location['states'])): ?> disabled <?php endif; ?>>
                                <option value="">Select state</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $new_location['states'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($s['id']); ?>"><?php echo e($s['name']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">City</label>
                            <select wire:model="new_location.city_id"
                                    class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-emerald-300 outline-none"
                                    <?php if(empty($new_location['cities'])): ?> disabled <?php endif; ?>>
                                <option value="">Select city</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $new_location['cities'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ci): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($ci['id']); ?>"><?php echo e($ci['name']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Postal Code</label>
                            <input wire:model="new_location.postal_code" type="text"
                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-emerald-300 outline-none">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Address Line 1 <span class="text-red-500">*</span></label>
                        <input wire:model="new_location.address_line_1" type="text"
                               class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-emerald-300 outline-none">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['new_location.address_line_1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="mt-3">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Address Line 2</label>
                        <input wire:model="new_location.address_line_2" type="text"
                               class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-emerald-300 outline-none">
                    </div>

                    <label class="flex items-center gap-2 mt-3 text-sm text-gray-700 cursor-pointer">
                        <input wire:model="new_location.is_primary" type="checkbox" class="rounded" style="accent-color:var(--theme-primary)">
                        Set as primary location
                    </label>

                    <div class="flex justify-end gap-2 mt-4">
                        <button @click="open = false"
                                class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                        <button wire:click="addLocation" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition" style="background:var(--theme-primary)">
                            <i class="fa-solid fa-plus"></i>
                            <span wire:loading.remove wire:target="addLocation">Add Location</span>
                            <span wire:loading wire:target="addLocation">Saving...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\livewire\buyer\buyer-profile-manager.blade.php ENDPATH**/ ?>