from . import views
from django.urls import path

urlpatterns = [
    path('', views.dashboard, name="dashboard"),
    path('contacts', views.add_contact, name="contacts"),

]