{{--
    Benefit code from its name: "Featured Supplier" → "featured_supplier".
    Matches SubscriptionBenefitRequest's rule (lowercase, digits, underscores,
    starting with a letter, max 100). Included by every benefit form.
--}}
@once
@push('scripts')
<script>
    function benefitCodeFromName(name) {
        let code = String(name || '')
            .normalize('NFKD').replace(/[̀-ͯ]/g, '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
        if (/^[0-9]/.test(code)) code = 'b_' + code;
        return code.slice(0, 100);
    }
</script>
@endpush
@endonce
