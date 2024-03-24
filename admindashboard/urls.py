from . import views
from django.urls import path

urlpatterns = [
    path('', views.dashboard, name="dashboard"),
    path('contacts', views.add_contact, name="contacts"),
    path('show_contacts', views.show_contacts, name="show_contacts"),
    path('show_contact/<int:contact_id>', views.show_contact, name="show_contact"),
    path('edit_contact/<int:contact_id>', views.edit_contact, name="edit_contact"),
    path('delete_contact/<int:contact_id>', views.delete_contact, name="delete_contact"),


]