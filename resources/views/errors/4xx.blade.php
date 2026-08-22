<x-error-page
    :code="$exception->getStatusCode() ?? null"
    icon="fa-triangle-exclamation"
    accent="amber"
    title="Request Error"
    :message="$exception->getMessage() ?: 'We couldn\'t process that request. Please check the address and try again.'"
/>
