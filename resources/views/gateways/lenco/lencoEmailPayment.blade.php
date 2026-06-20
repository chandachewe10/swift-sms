<script src="https://pay.lenco.co/js/v1/inline.js"></script>
<script>
    function makeEmailPayment() {
        LencoPay.getPaid({
            key: "{{ env('LENCO_PUBLIC_KEY') }}",
            reference: 'email-ref-' + Date.now(),
            email: "{{ auth()->user()->email }}",
            amount: 500,
            currency: "ZMW",
            channels: ["card", "mobile-money"],
            customer: {
                firstName: "{{ auth()->user()->name }}",
                lastName: "",
                phone: "",
            },
            onSuccess: function (response) {
                let formData = new FormData();
                formData.append("_token", "{{ csrf_token() }}");
                formData.append("data", JSON.stringify(response));

                fetch("{{ route('completeEmailSubscription') }}", {
                    method: "POST",
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        window.location.href = "/admin";
                    } else {
                        alert("Something went wrong: " + (data.message || ''));
                        window.location.href = "/admin";
                    }
                })
                .catch(error => {
                    alert("Failed to complete payment. Please try again.");
                    window.location.href = "/admin";
                });
            },
            onClose: function () {
                alert('Payment was not completed. Please try again.');
                window.location.href = "/admin";
            },
            onConfirmationPending: function () {
                alert('Your Email subscription will be activated once the payment is confirmed.');
            },
        });
    }

    window.addEventListener('DOMContentLoaded', function () {
        makeEmailPayment();
    });
</script>
