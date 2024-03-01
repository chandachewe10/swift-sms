from django.shortcuts import render, redirect
from django.contrib import messages
from django.contrib.auth import authenticate, login as login_user
from django.contrib.auth.decorators import login_required
from django.contrib.auth.forms import AuthenticationForm
from .forms import UserRegisterForm
from django.core.mail import send_mail
from django.core.mail import EmailMultiAlternatives
from django.template.loader import get_template
from django.template import Context


#################### index#######################################
def index(request):
	return render(request, 'user/index.html', {'title':'index'})

########### register here #####################################
def register(request):
	if request.method == 'POST':
		form = UserRegisterForm(request.POST)
		if form.is_valid():
			form.save()
			username = form.cleaned_data.get('username')
			email = form.cleaned_data.get('email')

			######################### mail system ####################################
			# htmly = get_template('auth/email_registration.html')
			# d = { 'username': username }
			# subject, from_email, to = 'welcome', 'info@bulksms.net', email
			# html_content = htmly.render(d)
			# msg = EmailMultiAlternatives(subject, html_content, from_email, [to])
			# msg.attach_alternative(html_content, "text/html")
			# msg.send()
			##################################################################
			messages.success(request, f'Your account has been created ! You are now able to log in')
			return redirect('login')
	else:
		form = UserRegisterForm()
	return render(request, 'auth/register.html', {'form': form, 'title':'Register'})

################ login forms###################################################
def login(request):
	if request.method == 'POST':

		# AuthenticationForm_can_also_be_used__

		username = request.POST['username']
		password = request.POST['password']
		user = authenticate(request, username = username, password = password)
		if user is not None:
			login_user(request, user)
			messages.success(request, f' welcome {username} !!')
			return redirect('login')
		else:
			messages.warning(request, f'these credentials do not match our records')
	form = AuthenticationForm()
	return render(request, 'auth/login.html', {'form':form, 'title':'Sign In'})
