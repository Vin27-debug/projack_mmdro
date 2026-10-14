class AuthResponse {
  final String accessToken;
  final String tokenType;

  AuthResponse({required this.accessToken, required this.tokenType});

  factory AuthResponse.fromJson(Map<String, dynamic> json) {
    final accessToken =
        json['access_token'] as String? ?? json['token'] as String? ?? '';
    if (accessToken.isEmpty) {
      throw const FormatException('The login response did not contain an access token.');
    }

    return AuthResponse(
      accessToken: accessToken,
      tokenType: json['token_type'] as String? ?? 'Bearer',
    );
  }
}
