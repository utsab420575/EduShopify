<x-error-page
    code="403"
    icon="fa-lock"
    accent="red"
    title="Access Denied"
    :message="$exception->getMessage() ?: 'You don\'t have permission to view this page. If you believe this is a mistake, please contact support or an administrator.'"
/>
