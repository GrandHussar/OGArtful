import moment from 'moment';

export function useDateUtils() {
  const convertTo12HourFormat = (time) => {
    if (!time) return '';
    const [hours, minutes] = time.split(':');
    const period = parseInt(hours, 10) >= 12 ? 'PM' : 'AM';
    const formattedHour = parseInt(hours, 10) % 12 || 12;
    return `${formattedHour}:${minutes} ${period}`;
  };

  const formatDate = (dateObj) => {
    const [year, month, day] = dateObj.date.split('-');
    const timeString = dateObj.time || '00:00:00';
    return `${month}/${day}/${year}, ${timeString}`;
  };

  return { convertTo12HourFormat, formatDate };
}
