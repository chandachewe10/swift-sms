from . import views
from django.urls import path

urlpatterns = [
    path('', views.landing_page_view, name="landing_page"),
    # path('details/<int:id>', views.student_details, name="details"),
]