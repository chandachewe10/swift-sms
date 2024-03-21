from django import forms
from crispy_forms.helper import FormHelper
from crispy_forms.layout import Layout, Row, Column, Field

class ContactsForm(forms.Form):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.helper = FormHelper()
        self.helper.form_id = 'contacts-form'
        self.helper.form_class = 'form-control'
        self.helper.form_method = 'post'
        self.helper.form_action = 'add-contact'

    firtsname = forms.CharField(
        label = "First Name",
        max_length = 255,
        required = True,
    )

    lastname = forms.CharField(
        label = "Last Name",
        max_length = 255,
        required = True,
    )
    email = forms.EmailField(
        label="Email",
        max_length=255,
        required=True,
    )

    address = forms.CharField(
        label="Address",
        max_length=255,
        required=False,
    )
    company = forms.CharField(
        label="Company",
        max_length=255,
        required=False,
    )

    tag = forms.CharField(
        label="Add Tag",
        max_length=255,
        required=False,
    )

