@props([
    'label' => '',
    'name' => '',
    'checked' => false,
    'color' => 'blue',
    'disabled' => false,
    'id' => null,
])

@php
    $id = $id ?? 'toggle-' . uniqid();

    // Color configurations dengan warna sesuai spesifikasi
    $colorMap = [
        'blue' => [
            'bg' => 'bg-blue-500',
            'bgDisabled' => 'bg-slate-200',
            'hover' => 'group-hover:text-blue-500',
            'ring' => 'focus:ring-blue-400',
        ],
        'purple' => [
            'bg' => 'bg-purple-600',
            'bgDisabled' => 'bg-slate-200',
            'hover' => 'group-hover:text-purple-600',
            'ring' => 'focus:ring-purple-500',
        ],
    ];

    $colors = $colorMap[$color] ?? $colorMap['blue'];
    $disabledClass = $disabled ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer';
    $initialChecked = $checked ? 'true' : 'false';
@endphp

<div class="flex items-center justify-between group {{ $disabledClass }}"
     x-data="{
         checked: {{ $initialChecked }},
         disabled: {{ $disabled ? 'true' : 'false' }},
         toggle() {
             if (!this.disabled) {
                 this.checked = !this.checked;
                 this.$refs.hiddenInput.value = this.checked ? '1' : '0';
                 this.$el.dispatchEvent(new CustomEvent('toggle-change', { detail: { checked: this.checked } }));
             }
         }
     }"
     @click="toggle()">

    <!-- Label dengan hover effect sesuai warna -->
    <label for="{{ $id }}"
           class="text-sm font-medium text-slate-700 {{ $colors['hover'] }} transition-colors duration-200 cursor-pointer select-none">
        {{ $label }}
    </label>

    <!-- Toggle Switch Container -->
    <div class="relative inline-flex items-center ml-4">

        <!-- Hidden input untuk form submission -->
        <input type="hidden"
               x-ref="hiddenInput"
               name="{{ $name }}"
               :value="checked ? '1' : '0'"
               value="{{ $checked ? '1' : '0' }}">

        <!-- Checkbox input (visually hidden) -->
        <input type="checkbox"
               x-ref="checkbox"
               id="{{ $id }}"
               name="{{ $name }}_checkbox"
               value="1"
               {{ $checked ? 'checked' : '' }}
               {{ $disabled ? 'disabled' : '' }}
               class="sr-only"
               @change="checked = $el.checked">

        <!-- Toggle background dengan ukuran 44px x 24px -->
        <div class="relative w-11 h-6 rounded-full transition-all duration-300 ease-in-out shadow-inner border"
             :class="[
                 checked ? '{{ $colors['bg'] }} border-transparent' : 'bg-slate-300 border-slate-300',
                 !disabled && checked && 'shadow-lg',
             ]">

            <!-- Toggle knob dengan ukuran 16px -->
            <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-all duration-300 ease-in-out shadow"
                 :class="checked ? 'translate-x-5' : 'translate-x-0'"></div>
        </div>
    </div>
</div>

<!-- Styles untuk sizing presisi dan animations -->
<style>
    /* Ukuran konsisten */
    [data-toggle-width="44px"] {
        width: 400px;
    }

    [data-toggle-height="24px"] {
        height: 24px;
    }

    [data-knob-size="16px"] {
        width: 16px;
        height: 16px;
    }

    /* Smooth animation dengan cubic-bezier */
    @media (prefers-reduced-motion: no-preference) {
        .toggle-bg {
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        }
    }
</style>
