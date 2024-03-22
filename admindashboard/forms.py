from django import forms
from django.db import models
from .models import Contacts
from crispy_forms.helper import FormHelper
from crispy_forms.layout import Layout, Row, Column, Submit

class ContactsForm(forms.ModelForm):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.helper = FormHelper()
        self.helper.form_id = 'contacts-form'
        self.helper.form_method = 'post'
        self.helper.form_action = 'add-contact'
        self.helper.form_class = 'form-control'

        self.helper.layout = Layout(
            Row(
                Column('firstname', css_class='form-group col-md-6 mb-0'),
                Column('lastname', css_class='form-group col-md-6 mb-0'),
                css_class='form-row'
            ),
            Row(
                Column('email', css_class='form-group col-md-6 mb-0'),
                Column('address', css_class='form-group col-md-6 mb-0'),
                css_class='form-row'
            ),
            Row(
                Column('phone', css_class='form-group col-md-12 mb-0'),

                css_class='form-row'
            ),
            Row(
                Column('company', css_class='form-group col-md-6 mb-0'),
                Column('tag', css_class='form-group col-md-6 mb-0'),
                css_class='form-row'
            ),

            Submit('submit', u'Add Contact', css_class='btn btn-success'),
        )

    firstname = forms.CharField(
        label="First Name",
        max_length=255,
        required=True,
        widget=forms.TextInput(attrs={'placeholder': 'John'})
    )

    lastname = forms.CharField(
        label="Last Name",
        max_length=255,
        required=True,
        widget=forms.TextInput(attrs={'placeholder': 'Doe'})
    )
    phone = forms.CharField(
        label="Phone",
        max_length=10,
        required=True,
        widget=forms.TextInput(attrs={'placeholder': 'Phone'})
    )

    email = forms.EmailField(
        label="Email",
        max_length=255,
        required=True,
        widget=forms.TextInput(attrs={'placeholder': 'johndoe@gmail.com'})
    )

    address = forms.CharField(
        label="Address",
        max_length=255,
        required=False,
        widget=forms.TextInput(attrs={'placeholder': '1234 Main St'})
    )

    company = forms.CharField(
        label="Company",
        max_length=255,
        required=False,
        widget=forms.TextInput(attrs={'placeholder': 'ABC Ltd'})
    )

    tag = forms.CharField(
        label="Add Tag",
        max_length=255,
        required=False,
        widget=forms.TextInput(attrs={'placeholder': 'Customer'})
    )

    def clean_email(self):
        email = self.cleaned_data.get('email')
        if Contacts.objects.filter(email=email).exists():
            raise forms.ValidationError("This email address is already in use.")
        return email

    class Meta:
        model = Contacts
        fields = ['firstname', 'lastname','phone','email', 'address','company','tag']

