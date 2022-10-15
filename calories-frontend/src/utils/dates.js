function format(year, month, day) {
    return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`
}

function dateBeforeDays(daysShift) {
    const today = new Date(new Date() - daysShift * 1000 * 60 * 60 * 24)
    return format(today.getFullYear(), today.getMonth() + 1, today.getDate())
}

function today() {
    return dateBeforeDays(0)
}

export default {
    format,
    today,
    dateBeforeDays
}