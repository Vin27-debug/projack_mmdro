import 'package:flutter/material.dart';

class AppTypography {
  AppTypography._();

  static const headline1 = TextStyle(
    fontFamily: 'Inter',
    fontWeight: FontWeight.w700,
    fontSize: 28,
    color: Color(0xFF0F172A),
  );

  static const headline2 = TextStyle(
    fontFamily: 'Inter',
    fontWeight: FontWeight.w600,
    fontSize: 22,
    color: Color(0xFF0F172A),
  );

  static const headline3 = TextStyle(
    fontFamily: 'Inter',
    fontWeight: FontWeight.w600,
    fontSize: 18,
    color: Color(0xFF0F172A),
  );

  static const bodyLarge = TextStyle(
    fontFamily: 'Inter',
    fontWeight: FontWeight.w500,
    fontSize: 16,
    color: Color(0xFF0F172A),
  );

  static const bodyMedium = TextStyle(
    fontFamily: 'Inter',
    fontWeight: FontWeight.w400,
    fontSize: 14,
    color: Color(0xFF475569),
  );

  static const bodySmall = TextStyle(
    fontFamily: 'Inter',
    fontWeight: FontWeight.w400,
    fontSize: 12,
    color: Color(0xFF475569),
  );

  static const label = TextStyle(
    fontFamily: 'Inter',
    fontWeight: FontWeight.w600,
    fontSize: 12,
    color: Color(0xFF0F172A),
    letterSpacing: 0.5,
  );
}
