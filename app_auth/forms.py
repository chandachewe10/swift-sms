from django import forms
from django.contrib.auth.models import User
from django.contrib.auth.forms import UserCreationForm
from crispy_forms.helper import FormHelper
from crispy_forms.layout import Layout, Row, Column, Field

class UserRegisterForm(UserCreationForm):
    email = forms.EmailField(required=True)  # Set required attribute to True

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.helper = FormHelper()
        self.helper.form_method = 'post'
        self.helper.form_class = 'form-control'
        self.fields['password1'].help_text = None
        self.fields['password2'].help_text = None

        self.helper.layout = Layout(
            Row(
                Column('username', css_class='form-group'),
                css_class='form-group'
            ),
            Row(
                Column('email', css_class='form-group'),
                css_class='form-group'
            ),
            Row(
                Column('password1', css_class='form-group'),
                css_class='form-group'
            ),
            Row(
                Column('password2', css_class='form-group'),
                css_class='form-group'
            ),
        )

    class Meta:
        model = User
        fields = ['username', 'email', 'password1', 'password2']
