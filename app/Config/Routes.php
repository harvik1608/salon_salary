<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('sign-in', 'Auth::index');
$routes->get('/', 'Auth::index');
$routes->post('submit-sign-in', 'Auth::submit_sign_in');
$routes->get('logout', 'Auth::logout');

$routes->get('dashboard', 'Dashboard::index');
$routes->get('load-salon-report', 'Dashboard::load_salon_report');
$routes->get('profile', 'Dashboard::profile');
$routes->post('submit-profile', 'Dashboard::submit_profile');
$routes->get('staff-attendance/(:any)', 'Dashboard::staff_attendance/$1');
$routes->post('load-staff-attendance/(:any)','Dashboard::load_staff_attendance/$1');
$routes->get('staff-rate-history/(:any)', 'Dashboard::staff_rate_history/$1');
$routes->post('load-staff-rate-history/(:any)','Dashboard::load_staff_rate_history/$1');
$routes->post('submit-staff-increment','Dashboard::submit_staff_increment');
$routes->delete('remove-increment/(:any)','Dashboard::remove_increment/$1');

$routes->get('load-salon-chart', 'Dashboard::load_salon_chart');
$routes->get('load-income-chart', 'Dashboard::load_income_chart');
$routes->get('load-monthly-summary-report', 'Dashboard::load_monthly_summary_report');
$routes->get('load-expense-chart', 'Dashboard::load_expense_chart');
$routes->get('mode-wise-export', 'Export_excel::mode_wise_export');
$routes->resource('accoutants');
$routes->resource('staffs');

$routes->resource('salons');
$routes->get('salon-mode-entries/(:any)','Salons::salon_mode_entry/$1');
$routes->post('load-salon-mode-entry/(:any)','Salons::load_salon_mode_entry/$1');
$routes->post('salon-monthly-report/(:any)','Salons::salon_monthly_report/$1');
$routes->get('summary-view','Salons::summary_view');
$routes->get('remove-staff-attendance','Salons::remove_staff_attendance');
$routes->get('save-staff-attendance','Salons::save_staff_attendance');
$routes->post('export-salon-report/(:any)','Salons::export_salon_report/$1');

$routes->resource('entries');
$routes->post('load-entries', 'Entries::load');
$routes->post('export-entries','Entries::export');
$routes->get('salon-entries/(:any)','Entries::salon_entry/$1');
$routes->get('get-ajax-form-entry','Entries::get_ajax_form_entry');
$routes->get('remove-daily-entry','Entries::remove_daily_entry');
$routes->post('salon-export-entries/(:any)','Entries::salon_export_entry/$1');
$routes->get('get-extra-salon-staff','Entries::get_extra_salon_staff');
$routes->get('remove-entry','Entries::remove_entry');


$routes->resource('attendances');
$routes->post('load-attendances', 'Attendances::load');
$routes->get('today-checkin', 'Attendances::today_checkin');
$routes->get('today-checkout', 'Attendances::today_checkout');
$routes->post('daily-checkout', 'Attendances::daily_checkout');
$routes->get('staff-attendances', 'Attendances::staff_attendances');
$routes->post('load-staff-attendances', 'Attendances::load_staff_attendance');

$routes->resource('reports');
$routes->post('load-reports', 'Reports::load');
$routes->get('daily-report/(:any)','Reports::daily_report/$1');
$routes->get('download-daily-report/(:any)','Reports::download_daily_report/$1');
$routes->get('yearly-reports','Reports::yearly_reports');
$routes->get('load-yearly-report','Reports::load_yearly_report');
$routes->get('save-staff-yearly-note','Reports::save_staff_yearly_note');

$routes->resource('salary_slips');
$routes->get('get-monthly-checkins','Salary_slips::get_monthly_checkins');

$routes->resource('payment_modes');
$routes->get('mode-wise-chart/(:any)','Payment_modes::mode_wise_chart/$1');