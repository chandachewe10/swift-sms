from django.shortcuts import render,redirect
from django.contrib.auth.decorators import login_required
from django.contrib import messages
from .forms import ContactsForm

# Create your views here.

@login_required(login_url='login')
def dashboard(request):
   return render(request, 'dashboard.html')
@login_required(login_url='login')
def add_contact(request):
   if request.method == 'GET':
      contact_form = ContactsForm()
      return render(request, 'add_contact.html',{'contact_form':contact_form})
   else:
      if request.method == 'POST':
         form = ContactsForm(request.POST)
         print(request.POST);
         if form.is_valid():
            form.save()
            messages.success(request, 'Your contact has been added successfully')
            return redirect('contacts')


