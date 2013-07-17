function unenroll_confirmed() {
    return confirm(student.unenroll_alert);
}

function unenroll() {
    if (unenroll_confirmed()) {
        return true;
    } else {
        return false;
    }
}