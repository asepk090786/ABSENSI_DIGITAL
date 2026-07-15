import 'package:intl/intl.dart';

class DateFormatter {
  static final String apiFormat = 'yyyy-MM-dd';
  static final String displayFormat = 'dd MMM yyyy';
  static final String fullDisplayFormat = 'dd MMMM yyyy';
  static final String timeFormat = 'HH:mm';

  static String formatApi(DateTime date) => DateFormat(apiFormat).format(date);

  static String formatDisplay(DateTime date) => DateFormat(displayFormat).format(date);

  static String formatFull(DateTime date) => DateFormat(fullDisplayFormat).format(date);

  static String formatTime(DateTime date) => DateFormat(timeFormat).format(date);

  static String formatDateTime(DateTime date) =>
      '${formatDisplay(date)}, ${formatTime(date)}';

  static String weekOfYear(DateTime date) {
    final week = DateFormat('w').format(date);
    final year = date.year.toString();
    return '$year-W$week';
  }
}
