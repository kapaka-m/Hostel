import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/endpoints.dart';
import 'package:hostel_mobile/src/api/response_parser.dart';
import 'package:hostel_mobile/src/models/ticket_comment_model.dart';
import 'package:hostel_mobile/src/models/ticket_model.dart';

class TicketRepository {
  final ApiClient _client;

  TicketRepository(this._client);

  Future<PaginatedResult<TicketModel>> fetchTickets({
    Map<String, dynamic>? filters,
    int page = 1,
  }) async {
    final query = {
      if (filters != null) ...filters,
      'page': page,
    };
    final response = await _client.get(Endpoints.tickets, query: query);
    return extractPaginated(response.data, TicketModel.fromJson);
  }

  Future<TicketModel> fetchTicket(int ticketId) async {
    final response = await _client.get(Endpoints.ticketDetails(ticketId));
    return TicketModel.fromJson(ensureMap(response.data));
  }

  Future<TicketModel> createTicket({
    required String subject,
    required String description,
    String? category,
    String? priority,
    String? status,
    int? assignedTo,
    int? dormId,
  }) async {
    final payload = {
      'subject': subject,
      'description': description,
      if (category != null) 'category': category,
      if (priority != null) 'priority': priority,
      if (status != null) 'status': status,
      if (assignedTo != null) 'assigned_to': assignedTo,
      if (dormId != null) 'dorm_id': dormId,
    };
    final response = await _client.post(Endpoints.tickets, data: payload);
    return TicketModel.fromJson(ensureMap(response.data));
  }

  Future<TicketModel> updateTicket(int ticketId, Map<String, dynamic> updates) async {
    final response = await _client.put(Endpoints.ticketDetails(ticketId), data: updates);
    return TicketModel.fromJson(ensureMap(response.data));
  }

  Future<TicketCommentModel> addComment(int ticketId, String body) async {
    final response = await _client.post(Endpoints.ticketComments(ticketId), data: {
      'body': body,
    });
    return TicketCommentModel.fromJson(ensureMap(response.data));
  }
}
