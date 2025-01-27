jQuery(document).ready(function($) {
    // คัดลอก meta key
    $('.copy-meta-key').on('click', function() {
        const metaKey = $(this).data('meta-key');
        $('#meta_key').val(metaKey);
    });

    // สร้างชื่อฟิลด์อัตโนมัติจาก label
    $('#field_label').on('input', function() {
        const label = $(this).val();
        const fieldName = label
            .toLowerCase()
            .replace(/[^a-z0-9]/g, '_')
            .replace(/_+/g, '_')
            .replace(/^_|_$/g, '');
        $('#field_name').val(fieldName);
    });

    // ลบฟิลด์
    $('.delete-field').on('click', function(e) {
        e.preventDefault();
        
        // Debug log
        console.log('Delete button clicked');
        console.log('Ajax URL:', thaitopAdminData.ajaxurl);
        
        if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบฟิลด์นี้?')) {
            return;
        }

        const fieldId = $(this).data('id');
        const row = $(this).closest('tr');
        const button = $(this);

        // เพิ่ม loading state
        button.text('กำลังลบ...').prop('disabled', true);

        $.ajax({
            url: thaitopAdminData.ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_custom_field',
                field_id: fieldId,
                nonce: thaitopAdminData.nonce
            },
            success: function(response) {
                console.log('Response:', response);
                
                if (response.success) {
                    row.fadeOut(400, function() {
                        $(this).remove();
                        if ($('.thaitop-fields-table tbody tr').length === 0) {
                            $('.thaitop-fields-table tbody').append(
                                '<tr><td colspan="6" style="text-align: center;">ไม่พบฟิลด์ที่กำหนดเอง</td></tr>'
                            );
                        }
                    });
                } else {
                    alert('ข้อผิดพลาด: ' + (response.data?.message || 'ข้อผิดพลาดที่ไม่ทราบสาเหตุ'));
                    button.text('ลบ').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    xhr: xhr,
                    status: status,
                    error: error
                });
                alert('เกิดข้อผิดพลาดของเครือข่าย กรุณาลองใหม่อีกครั้ง');
                button.text('ลบ').prop('disabled', false);
            }
        });
    });

    // การจัดการการส่งฟอร์ม
    $('form').on('submit', function(e) {
        if ($(this).find('[name="add_custom_field"]').length) {
            // ลบ preventDefault และการจัดการ AJAX
            // ให้ฟอร์มส่งตามปกติ
            return true;
        }
    });

    // เริ่มต้นตาราง sortable
    if ($('.thaitop-fields-table tbody').length) {
        $('.thaitop-fields-table tbody').sortable({
            handle: '.sort-handle',
            helper: fixWidthHelper,
            update: function(event, ui) {
                const items = [];
                $('.thaitop-fields-table tbody tr').each(function(index) {
                    items.push({
                        id: $(this).data('id'),
                        order: index
                    });
                });

                $.ajax({
                    url: thaitopAdminData.ajaxurl, // ใช้ URL จาก localized data
                    type: 'POST',
                    data: {
                        action: 'update_fields_order',
                        fields: items,
                        nonce: thaitopAdminData.nonce
                    },
                    success: function(response) {
                        if (!response.success) {
                            alert('ข้อผิดพลาดในการอัปเดตลำดับ: ' + (response.data?.message || 'ข้อผิดพลาดที่ไม่ทราบสาเหตุ'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', {xhr, status, error});
                        alert('เกิดข้อผิดพลาดของเครือข่ายขณะอัปเดตลำดับ');
                    }
                });
            }
        });
    }

    // แก้ไขส่วนของ color scheme selector
    $('#color-scheme-select').on('change', function() {
        const scheme = $(this).val();
        const customColors = $('.custom-colors-section');
        
        if (scheme === 'custom') {
            customColors.show();
        } else {
            customColors.hide();
            $(this).closest('form').submit(); // ส่งฟอร์มทันทีเมื่อเลือก scheme
        }
    });

    // เพิ่มการตรวจสอบเมื่อโหลดหน้า
    $(document).ready(function() {
        const currentScheme = $('#color-scheme-select').val();
        const customColors = $('.custom-colors-section');
        
        if (currentScheme === 'custom') {
            customColors.show();
        } else {
            customColors.hide();
        }
    });

    // ฟังก์ชันช่วยเหลือในการแก้ไขความกว้างของเซลล์ตารางขณะลาก
    function fixWidthHelper(e, ui) {
        ui.children().each(function() {
            $(this).width($(this).width());
        });
        return ui;
    }
});
