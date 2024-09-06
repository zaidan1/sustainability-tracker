function toggleAllPages(checked) 
{
    const checkboxes = document.querySelectorAll('input[name="st_display_pages[]"]:not(#all_pages)');
    checkboxes.forEach(checkbox => 
    {
        checkbox.checked = checked; 
    });
}
