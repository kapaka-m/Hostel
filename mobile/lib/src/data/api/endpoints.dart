class Endpoints {
  const Endpoints._();

  static const login = '/api/login';
  static const me = '/api/me';
  static const logout = '/api/logout';

  static const studentRoom = '/api/student/my-room';
  static const announcements = '/api/v1/announcements';
  static const tickets = '/api/v1/tickets';
  static const reportsOverview = '/api/v1/reports/overview';
  static const students = '/api/students';
  static const rooms = '/api/rooms';
  static const dorms = '/api/dorms';

  static String dormDetails(int dormId) => '/api/dorms/$dormId';

  static String roomDetails(int id) => '/api/rooms/$id';
  static String assignStudent(int roomId) => '/api/rooms/$roomId/assign-student';
  static String roomOccupants(int roomId) => '/api/rooms/$roomId/occupants';
  static String inviteDormAdmin(int dormId) => '/api/dorms/$dormId/create-dorm-admin';
  static String ticketDetails(int ticketId) => '/api/v1/tickets/$ticketId';
  static String ticketComments(int ticketId) => '/api/v1/tickets/$ticketId/comments';
}
